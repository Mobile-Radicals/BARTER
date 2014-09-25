package com.trade.barter;

import android.app.Activity;
import android.app.PendingIntent;
import android.content.Intent;
import android.content.IntentFilter;
import android.content.SharedPreferences;
import android.nfc.NfcAdapter;
import android.nfc.tech.IsoDep;
import android.nfc.tech.MifareClassic;
import android.nfc.tech.MifareUltralight;
import android.nfc.tech.Ndef;
import android.nfc.tech.NdefFormatable;
import android.nfc.tech.NfcA;
import android.nfc.tech.NfcB;
import android.nfc.tech.NfcF;
import android.nfc.tech.NfcV;
import android.os.Bundle;
import android.util.Log;
import android.view.Window;
import android.view.WindowManager;
import android.webkit.WebChromeClient;
import android.webkit.WebView;
import android.webkit.WebViewClient;
import android.widget.Toast;

public class SignUpActivity extends Activity {

    private WebView webView;

    private NfcAdapter adapter;
    private PendingIntent nfcPendingIntent;
    private IntentFilter[] readTagFilters;
    private String[][] mTechLists;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);

        getWindow().requestFeature(Window.FEATURE_PROGRESS);
        webView = new WebView(this);
        setContentView(webView);

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
        webView.loadUrl(getString(R.string.sign_up_url));

        //declare an NFC adapter
        adapter = NfcAdapter.getDefaultAdapter(this);
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

        if(adapter != null){
            //check if NFC is enabled
            boolean nfcEnabled = adapter.isEnabled();

            if(!nfcEnabled){
                Toast.makeText(getApplicationContext(), "Please activate NFC then press Back to return to the application!", Toast.LENGTH_LONG).show();
                startActivityForResult(new Intent(android.provider.Settings.ACTION_WIRELESS_SETTINGS), 0);
            }
        }
        nfcPendingIntent = PendingIntent.getActivity(this, 0, new Intent(this, getClass()).addFlags(Intent.FLAG_ACTIVITY_SINGLE_TOP), 0);

        //intent filter to handle NDEF NFC tags detected from within the application
        IntentFilter techDetected = new IntentFilter(NfcAdapter.ACTION_TECH_DISCOVERED);

        try{
            //try to catch all MIME types
            techDetected.addDataType("*/*");
        }
        catch (IntentFilter.MalformedMimeTypeException e) {
            throw new RuntimeException("could not add MIME type.", e);
        }

        readTagFilters = new IntentFilter[] {techDetected};

        mTechLists = new String[][] {
                new String[] {IsoDep.class.getName()},
                new String[] {NfcA.class.getName()},
                new String[] {NfcB.class.getName()},
                new String[] {NfcF.class.getName()},
                new String[] {NfcV.class.getName()},
                new String[] {Ndef.class.getName()},
                new String[] {NdefFormatable.class.getName()},
                new String[] {MifareClassic.class.getName()},
                new String[] {MifareUltralight.class.getName()}
        };

        //enable priority for current activity to detect scanned tags
        adapter.enableForegroundDispatch(this, nfcPendingIntent, readTagFilters, mTechLists);
    }

    @Override
    public void onBackPressed() {
        super.onBackPressed();
        finish();
    }
}
