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
import android.os.Handler;
import android.widget.Toast;

public class SplashscreenActivity extends Activity {

    private static int SPLASH_TIME_OUT = 3000;

    private SharedPreferences settings;
    private NfcAdapter adapter;
    private PendingIntent nfcPendingIntent;
    private IntentFilter[] readTagFilters;
    private String[][] mTechLists;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_splashsreen);

        //get the current shared preferences
        settings = this.getSharedPreferences(getString(R.string.preferences), 0);

        //declare an NFC adapter
        adapter = NfcAdapter.getDefaultAdapter(this);
    }

    @Override
    protected void onResume(){
        super.onResume();

        final SharedPreferences settings = this.getSharedPreferences(getString(R.string.preferences), 0);

        if(adapter != null){
            //check if NFC is enabled
            boolean nfcEnabled = adapter.isEnabled();

            if(!nfcEnabled){
                Toast.makeText(getApplicationContext(), "Please activate NFC then press Back to return to the application!", Toast.LENGTH_LONG).show();
                startActivityForResult(new Intent(android.provider.Settings.ACTION_WIRELESS_SETTINGS), 0);
            }
            else{
                new Handler().postDelayed(new Runnable() {
                    @Override
                    public void run() {

                        if(!settings.getBoolean("loggedIn", false)){
                            startActivity(new Intent(getApplicationContext(), SigninActivity.class));
                        }
                        else{
                            startActivity(new Intent(getApplicationContext(), MainActivity.class));
                        }

                        //close the splashscreen activity
                        finish();
                    }
                }, SPLASH_TIME_OUT);
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
