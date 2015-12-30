package com.trade.barter;

import android.app.Activity;
import android.app.PendingIntent;
import android.content.Intent;
import android.content.IntentFilter;
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
import android.widget.Toast;

public class NFCState {
	public static void check(Activity activity)
    {
    	while(!NfcAdapter.getDefaultAdapter(activity).isEnabled()){
            Toast.makeText(activity.getApplicationContext(), "Please activate NFC then press Back to return to the application!", Toast.LENGTH_LONG).show();
            activity.startActivityForResult(new Intent(android.provider.Settings.ACTION_WIRELESS_SETTINGS), 0);
        } 
    }

	public static void onPause(Activity activity)
	{
		if(NfcAdapter.getDefaultAdapter(activity) != null)
			NfcAdapter.getDefaultAdapter(activity).disableForegroundDispatch(activity);
	}
	
	public static void givePriority(Activity activity)
	{
		PendingIntent nfcPendingIntent = PendingIntent.getActivity(activity, 0, new Intent(activity, activity.getClass()).addFlags(Intent.FLAG_ACTIVITY_SINGLE_TOP), 0);
        IntentFilter techDetected = new IntentFilter(NfcAdapter.ACTION_TECH_DISCOVERED);

        try{
            //try to catch all MIME types
            techDetected.addDataType("*/*");
        }
        catch (IntentFilter.MalformedMimeTypeException e) {
            throw new RuntimeException("could not add MIME type.", e);
        }

        IntentFilter[] readTagFilters = new IntentFilter[] {techDetected};

        String[][] mTechLists = new String[][] {
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

        NfcAdapter.getDefaultAdapter(activity).enableForegroundDispatch(activity, nfcPendingIntent, readTagFilters, mTechLists);
	}
	
}
