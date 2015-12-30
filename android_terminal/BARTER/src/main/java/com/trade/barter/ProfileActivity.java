package com.trade.barter;

import android.app.Activity;
import android.content.Intent;
import android.content.SharedPreferences;
import android.graphics.drawable.ColorDrawable;
import android.nfc.NfcAdapter;
import android.os.Bundle;
import android.util.Log;
import android.view.Window;
import android.view.WindowManager;
import android.webkit.WebChromeClient;
import android.webkit.WebView;
import android.webkit.WebViewClient;
import android.widget.Toast;

import org.apache.http.util.EncodingUtils;

public class ProfileActivity extends Activity {

    private WebView webView;
    private SharedPreferences settings;

    private NfcAdapter adapter;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);

        getWindow().requestFeature(Window.FEATURE_PROGRESS);
        webView = new WebView(this);
        setContentView(webView);

        //declare an NFC adapter
        adapter = NfcAdapter.getDefaultAdapter(this);

        this.getActionBar().setDisplayHomeAsUpEnabled(true);
        this.getActionBar().setBackgroundDrawable(new ColorDrawable(getResources().getColor(R.color.light_blue)));

        settings = getSharedPreferences(getString(R.string.preferences), 0);
        String credentials = "email=" + settings.getString("email", null);
        credentials += "&password=" + settings.getString("password", null);

        Log.i("TEST", credentials);

        //stop the soft keyboard from popping up
        this.getWindow().setSoftInputMode(WindowManager.LayoutParams.SOFT_INPUT_STATE_ALWAYS_HIDDEN);

        webView.getSettings().setJavaScriptEnabled(true);

        final Activity activity = this;
        webView.setWebChromeClient(new WebChromeClient(){
            @Override
            public void onProgressChanged(WebView view, int newProgress) {
                activity.setProgress(newProgress * 1000);
            }
        });

        webView.setWebViewClient(new WebViewClient(){
            public void onReceivedError(WebView view, int errorCode, String description, String failingUrl) {
                //super.onReceivedError(view, errorCode, description, failingUrl);
                Toast.makeText(activity, description, Toast.LENGTH_LONG).show();
            }
        });

        //Load the page
        webView.postUrl(getString(R.string.profile_url), EncodingUtils.getBytes(credentials, "BASE64"));
    }


    @Override
    protected void onNewIntent(Intent intent){
        Log.d(getString(R.string.app_name), "NFC intent was discovered");
    }

    @Override
    protected void onResume(){
        super.onResume();

        SharedPreferences settings = this.getSharedPreferences(getString(R.string.preferences), 0);
        if(!settings.getBoolean("loggedIn", false)){
            startActivity(new Intent(this, SigninActivity.class));
        }

        if(adapter != null)
            NFCState.check(this);
       
        NFCState.givePriority(this);
    }

    @Override
    public void onBackPressed() {
        super.onBackPressed();
        finish();
    }
}
