package com.papyon.kurye

import android.Manifest
import android.annotation.SuppressLint
import android.app.Activity
import android.content.Intent
import android.content.pm.PackageManager
import android.net.Uri
import android.os.Bundle
import android.os.Environment
import android.provider.MediaStore
import android.view.View
import android.webkit.*
import android.widget.ProgressBar
import android.widget.Toast
import androidx.appcompat.app.AppCompatActivity
import androidx.core.app.ActivityCompat
import androidx.core.content.ContextCompat
import androidx.core.content.FileProvider
import androidx.swiperefreshlayout.widget.SwipeRefreshLayout
import java.io.File
import java.io.IOException
import java.text.SimpleDateFormat
import java.util.*

class MainActivity : AppCompatActivity() {

    private lateinit var webView: WebView
    private lateinit var progressBar: ProgressBar
    private lateinit var swipeRefresh: SwipeRefreshLayout
    
    private var filePathCallback: ValueCallback<Array<Uri>>? = null
    private var cameraPhotoPath: String? = null
    
    companion object {
        private const val FILECHOOSER_RESULTCODE = 1
        private const val PERMISSION_REQUEST_CODE = 100
        private const val APP_URL = "https://papyon.iksoft.com.tr/courier/login"
    }

    @SuppressLint("SetJavaScriptEnabled")
    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        setContentView(R.layout.activity_main)
        
        // İzinleri kontrol et
        checkPermissions()
        
        progressBar = findViewById(R.id.progressBar)
        swipeRefresh = findViewById(R.id.swipeRefresh)
        webView = findViewById(R.id.webView)
        
        // SwipeRefresh ayarları
        swipeRefresh.setColorSchemeColors(
            ContextCompat.getColor(this, android.R.color.black)
        )
        swipeRefresh.setOnRefreshListener {
            webView.reload()
        }
        
        // WebView ayarları
        webView.settings.apply {
            javaScriptEnabled = true
            domStorageEnabled = true
            databaseEnabled = true
            setGeolocationEnabled(true)
            allowFileAccess = true
            allowContentAccess = true
            loadWithOverviewMode = true
            useWideViewPort = true
            builtInZoomControls = false
            displayZoomControls = false
            cacheMode = WebSettings.LOAD_DEFAULT
            mixedContentMode = WebSettings.MIXED_CONTENT_ALWAYS_ALLOW
            mediaPlaybackRequiresUserGesture = false
        }
        
        // WebView client
        webView.webViewClient = object : WebViewClient() {
            override fun shouldOverrideUrlLoading(view: WebView?, request: WebResourceRequest?): Boolean {
                val url = request?.url.toString()
                // Harici linkleri tarayıcıda aç
                if (!url.contains("papyon.iksoft.com.tr")) {
                    startActivity(Intent(Intent.ACTION_VIEW, Uri.parse(url)))
                    return true
                }
                return false
            }
            
            override fun onPageFinished(view: WebView?, url: String?) {
                super.onPageFinished(view, url)
                swipeRefresh.isRefreshing = false
                progressBar.visibility = View.GONE
            }
            
            override fun onReceivedError(view: WebView?, request: WebResourceRequest?, error: WebResourceError?) {
                super.onReceivedError(view, request, error)
                swipeRefresh.isRefreshing = false
            }
        }
        
        // Chrome client (dosya yükleme ve konum için)
        webView.webChromeClient = object : WebChromeClient() {
            
            override fun onProgressChanged(view: WebView?, newProgress: Int) {
                progressBar.progress = newProgress
                if (newProgress == 100) {
                    progressBar.visibility = View.GONE
                } else {
                    progressBar.visibility = View.VISIBLE
                }
            }
            
            // Konum izni
            override fun onGeolocationPermissionsShowPrompt(
                origin: String?,
                callback: GeolocationPermissions.Callback?
            ) {
                callback?.invoke(origin, true, false)
            }
            
            // Dosya seçici (fotoğraf yükleme)
            override fun onShowFileChooser(
                webView: WebView?,
                filePathCallback: ValueCallback<Array<Uri>>?,
                fileChooserParams: FileChooserParams?
            ): Boolean {
                this@MainActivity.filePathCallback?.onReceiveValue(null)
                this@MainActivity.filePathCallback = filePathCallback
                
                // Kamera intent
                var takePictureIntent: Intent? = Intent(MediaStore.ACTION_IMAGE_CAPTURE)
                if (takePictureIntent?.resolveActivity(packageManager) != null) {
                    var photoFile: File? = null
                    try {
                        photoFile = createImageFile()
                        takePictureIntent.putExtra("PhotoPath", cameraPhotoPath)
                    } catch (ex: IOException) {
                        // Hata
                    }
                    
                    if (photoFile != null) {
                        cameraPhotoPath = "file:" + photoFile.absolutePath
                        takePictureIntent.putExtra(
                            MediaStore.EXTRA_OUTPUT,
                            Uri.fromFile(photoFile)
                        )
                    } else {
                        takePictureIntent = null
                    }
                }
                
                // Galeri intent
                val contentSelectionIntent = Intent(Intent.ACTION_GET_CONTENT)
                contentSelectionIntent.addCategory(Intent.CATEGORY_OPENABLE)
                contentSelectionIntent.type = "image/*"
                
                // Intent seçici
                val intentArray: Array<Intent?> = takePictureIntent?.let { arrayOf(it) } ?: arrayOfNulls(0)
                
                val chooserIntent = Intent(Intent.ACTION_CHOOSER)
                chooserIntent.putExtra(Intent.EXTRA_INTENT, contentSelectionIntent)
                chooserIntent.putExtra(Intent.EXTRA_TITLE, "Fotoğraf Seç")
                chooserIntent.putExtra(Intent.EXTRA_INITIAL_INTENTS, intentArray)
                
                startActivityForResult(chooserIntent, FILECHOOSER_RESULTCODE)
                return true
            }
        }
        
        // Sayfayı yükle
        webView.loadUrl(APP_URL)
    }
    
    @Throws(IOException::class)
    private fun createImageFile(): File {
        val timeStamp = SimpleDateFormat("yyyyMMdd_HHmmss", Locale.getDefault()).format(Date())
        val imageFileName = "JPEG_" + timeStamp + "_"
        val storageDir = getExternalFilesDir(Environment.DIRECTORY_PICTURES)
        return File.createTempFile(imageFileName, ".jpg", storageDir)
    }
    
    private fun checkPermissions() {
        val permissions = arrayOf(
            Manifest.permission.ACCESS_FINE_LOCATION,
            Manifest.permission.ACCESS_COARSE_LOCATION,
            Manifest.permission.CAMERA,
            Manifest.permission.READ_EXTERNAL_STORAGE
        )
        
        val permissionsToRequest = permissions.filter {
            ContextCompat.checkSelfPermission(this, it) != PackageManager.PERMISSION_GRANTED
        }
        
        if (permissionsToRequest.isNotEmpty()) {
            ActivityCompat.requestPermissions(
                this,
                permissionsToRequest.toTypedArray(),
                PERMISSION_REQUEST_CODE
            )
        }
    }
    
    override fun onActivityResult(requestCode: Int, resultCode: Int, data: Intent?) {
        super.onActivityResult(requestCode, resultCode, data)
        
        if (requestCode == FILECHOOSER_RESULTCODE) {
            if (filePathCallback == null) return
            
            var results: Array<Uri>? = null
            
            if (resultCode == Activity.RESULT_OK) {
                if (data == null) {
                    // Kameradan fotoğraf
                    cameraPhotoPath?.let {
                        results = arrayOf(Uri.parse(it))
                    }
                } else {
                    // Galeriden seçim
                    data.dataString?.let {
                        results = arrayOf(Uri.parse(it))
                    }
                }
            }
            
            filePathCallback?.onReceiveValue(results)
            filePathCallback = null
        }
    }
    
    override fun onBackPressed() {
        if (webView.canGoBack()) {
            webView.goBack()
        } else {
            super.onBackPressed()
        }
    }
}
