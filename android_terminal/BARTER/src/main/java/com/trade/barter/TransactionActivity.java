package com.trade.barter;

import android.app.ActionBar;
import android.app.Activity;
import android.app.AlertDialog;
import android.app.PendingIntent;
import android.content.Context;
import android.content.Intent;
import android.content.IntentFilter;
import android.content.IntentSender;
import android.content.SharedPreferences;
import android.graphics.drawable.ColorDrawable;
import android.location.Location;
import android.location.LocationManager;
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
import android.provider.Settings;
import android.util.Log;
import android.view.View;
import android.view.WindowManager;
import android.widget.EditText;
import android.widget.RatingBar;
import android.widget.TextView;
import android.widget.Toast;
import android.widget.ToggleButton;

import com.google.android.gms.common.ConnectionResult;
import com.google.android.gms.common.GooglePlayServicesClient;
import com.google.android.gms.common.GooglePlayServicesUtil;
import com.google.android.gms.location.LocationClient;
import com.google.android.gms.location.LocationListener;
import com.google.android.gms.location.LocationRequest;
import com.trade.barter.utils.ConsumerData;
import com.trade.barter.utils.DatabaseHandler;
import com.trade.barter.utils.Transaction;
import com.trade.barter.utils.Utils;

public class TransactionActivity extends Activity implements LocationListener, GooglePlayServicesClient.ConnectionCallbacks, GooglePlayServicesClient.OnConnectionFailedListener {

    private NfcAdapter adapter;
    private PendingIntent nfcPendingIntent;
    private IntentFilter[] readTagFilters;
    private String[][] mTechLists;
    private ActionBar actionBar;
    private String nfcCardID;
    AlertDialog dialog = null;
    private LocationManager locationManager;
    private boolean shareLocation = true;

    //location variables
    private LocationRequest mLocationRequest;
    private LocationClient mLocationClient;
    boolean mUpdatesRequested = true;

    public final static int CONNECTION_FAILURE_RESOLUTION_REQUEST = 9000;
    public static final int MILLISECONDS_PER_SECOND = 1000;
    public static final int UPDATE_INTERVAL_IN_SECONDS = 5;
    public static final int FAST_CEILING_IN_SECONDS = 1;
    public static final long UPDATE_INTERVAL_IN_MILLISECONDS = MILLISECONDS_PER_SECOND * UPDATE_INTERVAL_IN_SECONDS;
    public static final long FAST_INTERVAL_CEILING_IN_MILLISECONDS = MILLISECONDS_PER_SECOND * FAST_CEILING_IN_SECONDS;
    public Location currentLocation = null;

    private int points;
    private double price;
    private String type;

    private DatabaseHandler db;
    private SharedPreferences settings;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.transaction_main);

        //stop the soft keyboard from popping up
        this.getWindow().setSoftInputMode(WindowManager.LayoutParams.SOFT_INPUT_STATE_ALWAYS_HIDDEN);

        adapter = NfcAdapter.getDefaultAdapter(this);
        nfcPendingIntent = PendingIntent.getActivity(this, 0, new Intent(this, TransactionActivity.class).addFlags(Intent.FLAG_ACTIVITY_SINGLE_TOP), 0);

        actionBar = this.getActionBar();
        nfcCardID = getIntent().getExtras().getString("nfcCardID");
        actionBar.setTitle(nfcCardID);
        actionBar.setBackgroundDrawable(new ColorDrawable(getResources().getColor(R.color.barter_green)));
        actionBar.setDisplayHomeAsUpEnabled(true);

        // Create a new global location parameters object
        mLocationRequest = LocationRequest.create();
        mLocationRequest.setInterval(UPDATE_INTERVAL_IN_MILLISECONDS);
        mLocationRequest.setPriority(LocationRequest.PRIORITY_HIGH_ACCURACY);
        mLocationRequest.setFastestInterval(FAST_INTERVAL_CEILING_IN_MILLISECONDS);
        mLocationClient = new LocationClient(this, this, this);

        locationManager = (LocationManager)getSystemService(Context.LOCATION_SERVICE);

        //instantiate the database
        db = new DatabaseHandler(this.getApplicationContext());
        //get the current shared preferences
        settings = this.getSharedPreferences(getString(R.string.preferences), 0);

        //display the current consumer data
        ConsumerData consumerData = db.getConsumerData(nfcCardID);
        ((TextView) findViewById(R.id.loyaltyValue)).setText(String.valueOf(consumerData.getTotalPoints()));
        ((TextView) findViewById(R.id.numberValue)).setText(String.valueOf(consumerData.getTotalTransactions()));
        ((TextView) findViewById(R.id.spendValue)).setText(Utils.convertPrice(consumerData.getTotalSpent()));

        //set the type of the trader based on their online settings
        String businessProduce = settings.getString("businessProduce", null);
        Log.d(getString(R.string.app_name), "The business - " + businessProduce);

        //set the business operation of the current trader based on the shared pref settings
        if(businessProduce != null){
            if(businessProduce.equals("goods")){
                //trader activity has been set to goods
                ((ToggleButton) findViewById(R.id.goodsToggle)).setChecked(true);
            }
            if(businessProduce.equals("services")){
                //trader activity has been set to goods
                ((ToggleButton) findViewById(R.id.servicesToggle)).setChecked(true);
            }
            if(businessProduce.equals("both")){
                //trader activity has been set to services
                ((ToggleButton) findViewById(R.id.goodsToggle)).setChecked(true);
                ((ToggleButton) findViewById(R.id.servicesToggle)).setChecked(true);
            }
        }

        if(!locationManager.isProviderEnabled(LocationManager.GPS_PROVIDER) && !locationManager.isProviderEnabled(LocationManager.NETWORK_PROVIDER)){
            checkLocation();
        }

        findViewById(R.id.recordTransactionBtn).setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View view) {
                //get the data to be saved in the database
                points = (int)((RatingBar) findViewById(R.id.transactionRatingBar)).getRating();

                Log.d("TEST", "Rating " + points);

                String priceString = ((EditText) findViewById(R.id.transactionValue)).getText().toString();
                price = 0.00;
                if(priceString.length() > 0){
                    price = Utils.convertPrice(priceString);
                }

                ToggleButton goodsBtn = (ToggleButton) findViewById(R.id.goodsToggle);
                ToggleButton servicesBtn = (ToggleButton) findViewById(R.id.servicesToggle);

                if(goodsBtn.isChecked() && !servicesBtn.isChecked()){
                    type = "goods";
                }
                else if (!goodsBtn.isChecked() && servicesBtn.isChecked()){
                    type = "services";
                }
                else if(goodsBtn.isChecked() && servicesBtn.isChecked()){
                    type = "both";
                }
                else{
                    Toast.makeText(view.getContext(), "Please select at least one transaction type.", Toast.LENGTH_LONG).show();
                    return;
                }

                //get the transaction type
                String trans_type = settings.getString("trans_type", null);
                Log.i(getString(R.string.app_name), "The type of the transaction: " + trans_type);

                //get the last known location
                getLocation();

                if(!shareLocation){
                    db.addTransaction(new Transaction(settings.getString("cardId", null), nfcCardID, 0.00, 0.00, type, trans_type, price, points, Utils.getCurrentDate(), false));
                    Log.d(getString(R.string.app_name), "location null");
                }
                else{
                    if(currentLocation==null){
                        db.addTransaction(new Transaction(settings.getString("cardId", null), nfcCardID, 0.00, 0.00, type, trans_type, price, points, Utils.getCurrentDate(), false));
                    }
                    else{
                        db.addTransaction(new Transaction(settings.getString("cardId", null), nfcCardID, currentLocation.getLatitude(), currentLocation.getLongitude(), type, trans_type, price, points, Utils.getCurrentDate(), false));
                    }
                }

                db.addConsumerData(new ConsumerData(nfcCardID, price, points, 1, Utils.getCurrentDate()));
                Toast.makeText(view.getContext(), "Your transaction has been successfully saved.", Toast.LENGTH_SHORT).show();
                startActivity(new Intent(getApplicationContext(), MainActivity.class).addFlags(Intent.FLAG_ACTIVITY_CLEAR_TOP));
                finish();
            }
        });
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
    protected void onPause(){
        super.onPause();
        if(adapter != null)
            adapter.disableForegroundDispatch(this);
    }

    public void checkLocation(){

        final View dialogLayout = getLayoutInflater().inflate(R.layout.confirm_dialog, null);

        AlertDialog.Builder builder = new AlertDialog.Builder(this);
        builder.setView(dialogLayout);

        ((TextView) dialogLayout.findViewById(R.id.confirmDialogTitle)).setText(getString(R.string.location_title));
        ((TextView) dialogLayout.findViewById(R.id.confirmDialogDesc)).setText(getString(R.string.location_desc_one));
        ((TextView) dialogLayout.findViewById(R.id.confirmDialogDesc2)).setText("");
        ((TextView) dialogLayout.findViewById(R.id.confirmDialogFooterDesc)).setText("");

        (dialogLayout.findViewById(R.id.cancelBtn)).setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View view) {
                dialog.dismiss();
            }
        });

        (dialogLayout.findViewById(R.id.okBtn)).setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View view) {
                startActivityForResult(new Intent(Settings.ACTION_LOCATION_SOURCE_SETTINGS), 0);
                dialog.cancel();
            }
        });

        this.dialog = builder.create();
        this.dialog.show();
    }

    @Override
    protected void onActivityResult(final int requestCode, final int resultCode, final Intent data){
        if(!locationManager.isProviderEnabled(LocationManager.GPS_PROVIDER) && !locationManager.isProviderEnabled(LocationManager.NETWORK_PROVIDER)){
            checkLocation();
        }
        else{
            shareLocation = true;
            mLocationClient.connect();
        }
    }

    @Override
    protected void onStart() {
        super.onStart();
        mLocationClient.connect();
    }

    @Override
    protected void onStop() {
        if (mLocationClient.isConnected()) {
            stopPeriodicUpdates();
        }
        mLocationClient.disconnect();
        super.onStop();
    }

    @Override
    public void onConnected(Bundle bundle) {
        Log.d(getString(R.string.app_name), "Connected!");
        if (mUpdatesRequested) {
            startPeriodicUpdates();
        }
    }

    @Override
    public void onDisconnected() {
        Log.d(getString(R.string.app_name), "Disconnected!");
    }

    @Override
    public void onLocationChanged(Location location) {
        Log.d(getString(R.string.app_name), "Location Changed");
    }

    @Override
    public void onConnectionFailed(ConnectionResult connectionResult) {
        if (connectionResult.hasResolution()) {
            try {
                // Start an Activity that tries to resolve the error
                connectionResult.startResolutionForResult(this, CONNECTION_FAILURE_RESOLUTION_REQUEST);
            } catch (IntentSender.SendIntentException e) {
                // Log the error
                e.printStackTrace();
            }
        } else {
            // If no resolution is available, display a dialog to the user with the error.
            Log.d(getString(R.string.app_name), "Error");
        }
    }

    private void startPeriodicUpdates() {
        mLocationClient.requestLocationUpdates(mLocationRequest, this);
    }

    private void stopPeriodicUpdates() {
        mLocationClient.removeLocationUpdates(this);
    }

    public void getLocation(){
        if(servicesConnected()){
            currentLocation = mLocationClient.getLastLocation();
        }
    }

    private boolean servicesConnected() {
        int resultCode = GooglePlayServicesUtil.isGooglePlayServicesAvailable(this);

        if (ConnectionResult.SUCCESS == resultCode) {
            Log.d(getString(R.string.app_name), "Google Play Services Available!");
            return true;
        } else {
            Log.d(getString(R.string.app_name), "Google Play Services Unavailable!");
            return false;
        }
    }
}
