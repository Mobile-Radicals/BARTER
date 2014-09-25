package com.trade.barter;

import android.app.Activity;
import android.app.ProgressDialog;
import android.content.Intent;
import android.content.SharedPreferences;
import android.net.Uri;
import android.os.AsyncTask;
import android.os.Bundle;
import android.util.Log;
import android.view.View;
import android.view.WindowManager;
import android.widget.EditText;
import android.widget.Toast;

import com.trade.barter.utils.DatabaseHandler;
import com.trade.barter.utils.Utils;

import org.apache.http.HttpEntity;
import org.apache.http.HttpResponse;
import org.apache.http.client.HttpClient;
import org.apache.http.client.methods.HttpPost;
import org.apache.http.entity.StringEntity;
import org.apache.http.impl.client.DefaultHttpClient;
import org.json.JSONArray;
import org.json.JSONObject;

import java.io.BufferedReader;
import java.io.InputStream;
import java.io.InputStreamReader;

public class SigninActivity extends Activity {

    private ProgressDialog dialog;
    private String email, password;
    private SharedPreferences settings;
    private DatabaseHandler db;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.signin_main);

        //hide the action bar
        getActionBar().hide();
        //stop the soft keyboard from popping up
        this.getWindow().setSoftInputMode(WindowManager.LayoutParams.SOFT_INPUT_STATE_ALWAYS_HIDDEN);

        settings = this.getSharedPreferences(getString(R.string.preferences), 0);
        db = new DatabaseHandler(this);

        findViewById(R.id.loginBtn).setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View view) {
                email = ((EditText) findViewById(R.id.signinEmailInput)).getText().toString();
                password = ((EditText) findViewById(R.id.signinPasswordInput)).getText().toString();

                //dummy credentials
//                email = "marklochrie50265@gmail.com";
//                password = "g00gle";

                password = Utils.md5(password);
                //verify the network connection - if successful login the trader
                if(Utils.checkConnectivity(getApplicationContext())) loginTrader();
            }
        });

        findViewById(R.id.signupBtn).setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View view) {
                startActivity(new Intent(Intent.ACTION_VIEW, Uri.parse(getString(R.string.sign_up_url))));
            }
        });
    }

    public void loginTrader(){

        dialog = new ProgressDialog(SigninActivity.this);
        dialog.setMessage("Logging in...");
        dialog.setIndeterminate(true);
        dialog.setCancelable(false);
        dialog.show();

        JSONObject traderLogin = new JSONObject();
        try {
            traderLogin.put("email", email);
            traderLogin.put("password", password);
        } catch (Exception e) {
            Log.i("Exception while uploading the trader's credentials", e.getMessage());
        }

        //send the data
        String dataToSend = traderLogin.toString();

        //create the parameters to be passed to the network handler
        String[] params = new String[2];
        params[0] = getString(R.string.login_url);
        params[1] = dataToSend;

        //send the consumers' data to the server
        LoginTrader getTraderDetails = new LoginTrader();
        getTraderDetails.executeOnExecutor(AsyncTask.SERIAL_EXECUTOR, params);
    }

    private class LoginTrader extends AsyncTask<String, Void, String>{

        protected StringBuilder sb;
        protected String result;
        protected InputStream is;

        @Override
        protected void onPreExecute() {}

        @Override
        protected String doInBackground(String... params) {
            try {
                HttpClient client = new DefaultHttpClient();
                HttpPost post = new HttpPost(params[0]);

                post.setHeader("Content-type", "application/json");
                StringEntity se = new StringEntity(params[1]);
                post.setEntity(se);
                //set the response
                HttpResponse response = client.execute(post);
                HttpEntity entity = response.getEntity();
                is = entity.getContent();
            }
            catch (Exception e) {
                e.printStackTrace();
                Log.e("Http connection error", "Error connecting "+e.getMessage());
            }

            //handle the response
            try {
                BufferedReader reader = new BufferedReader(new InputStreamReader(is, "iso-8859-1"), 8);
                sb = new StringBuilder();
                sb.append(reader.readLine() + "\n");
                String line;

                while ((line = reader.readLine()) != null) {
                    sb.append(line + "\n");
                }
                reader.close();
                is.close();
                result = sb.toString();
                return result;
            } catch (Exception e) {
                Log.e(getString(R.string.app_name), "Error converting result " + e.toString());
                return null;
            }
        }

        @Override
        protected void onPostExecute(String result) {

            try{
                JSONObject allData = new JSONObject(result);
                Boolean received = allData.getBoolean("message");

                if(received){

                    JSONObject traderDetails = allData.getJSONObject("details");
                    JSONObject traderStats = allData.getJSONObject("traderTotals");
                    JSONArray consumersToBeUpdated = allData.getJSONArray("customerData");

                    try {

                        //save the details of the trader to the shared preferences object
                        SharedPreferences.Editor editor = settings.edit();

                        //add the values to the shared pref settings
                        editor.putInt("id", traderDetails.getInt("id"));
                        editor.putString("name", traderDetails.getString("name"));
                        editor.putString("cardId", traderDetails.getString("cardId"));
                        editor.putString("gender", traderDetails.getString("gender"));
                        editor.putString("dob", traderDetails.getString("dob"));
                        editor.putString("email", email);
                        editor.putString("password", Utils.md5(password));
                        editor.putString("postcode", traderDetails.getString("postcode"));
                        editor.putString("preferences", traderDetails.getString("preferences"));
                        editor.putBoolean("isManufacturer", Utils.convertIntToBoolean(traderDetails.getInt("isManufacturer")));
                        editor.putBoolean("isRetailer", Utils.convertIntToBoolean(traderDetails.getInt("isRetailer")));
                        editor.putBoolean("isService", Utils.convertIntToBoolean(traderDetails.getInt("isService")));
                        editor.putBoolean("fixed", Utils.convertIntToBoolean(traderDetails.getInt("fixed")));
                        editor.putBoolean("nomadic", Utils.convertIntToBoolean(traderDetails.getInt("nomadic")));
                        editor.putString("goodsServices", traderDetails.getString("goodsServices"));
                        editor.putString("statement", traderDetails.getString("statement"));

                        if(Utils.convertIntToBoolean(traderDetails.getInt("isManufacturer"))){
                            editor.putString("businessProduce", "goods");
                        }
                        if(Utils.convertIntToBoolean(traderDetails.getInt("isRetailer"))){
                            editor.putString("businessProduce", "both");
                        }
                        if(Utils.convertIntToBoolean(traderDetails.getInt("isService"))){
                            editor.putString("businessProduce", "services");
                        }

                        editor.putBoolean("loggedIn", true);

                        //editor.putString("totalTrans", traderStats.getString("total_trans"));
                        editor.putInt("totalMobileTransactions", traderStats.getInt("total_mobile_trans"));
                        editor.putInt("totalWebTransactions", traderStats.getInt("total_web_trans"));

                        editor.putInt("totalNonBarterTransactions", traderStats.getInt("total_non_barter_trans"));
                        editor.putInt("totalNonLocalTransactions", traderStats.getInt("total_non_local_trans"));

                        editor.putString("totalPriceGoods", traderStats.getString("total_price_goods"));
                        editor.putString("totalPriceServices", traderStats.getString("total_price_services"));
                        editor.putString("totalPriceBoth", traderStats.getString("total_price_both"));
                        editor.putString("lastUploaded", Utils.convertServerTimestamp(traderStats.getString("last_uploaded")));
                        editor.putString("lastCheck", Utils.convertServerTimestamp(traderStats.getString("last_uploaded")));

                        editor.putBoolean("needsUpdating", true);

                        editor.commit();

                        Log.e("LOGIN", settings.getString("lastUploaded", "Last updated"));

                        for(int i = 0; i < consumersToBeUpdated.length(); i++){
                            JSONObject consumer = consumersToBeUpdated.getJSONObject(i);
                            db.updateConsumerStats(consumer.getString("customer_id"), consumer.getInt("customer_spend"), consumer.getInt("customer_points"), consumer.getInt("customer_occurrences"), consumer.getString("timestamp"));
                        }

                        dialog.dismiss();

                        //close down the activity
                        startActivity(new Intent(getApplicationContext(), MainActivity.class));
                        finish();

                    } catch (Exception e) {
                        dialog.dismiss();
                        Log.e(getString(R.string.app_name), e.getMessage());
                    }
                }
                else{
                    //there was a problem saving the trader's details
                    dialog.dismiss();
                    Toast.makeText(getApplicationContext(), "Wrong email or password! Please try again!", Toast.LENGTH_LONG).show();
                    Log.d(getString(R.string.app_name), "Errors have been encountered while downloading the trader's details.");
                }
            }
            catch (Exception e) {
                e.printStackTrace();
                dialog.dismiss();
                Log.i(getString(R.string.app_name), "An error has been encountered while receiving the JSON");
            }
        }
    }

    @Override
    public void onBackPressed() {
        startActivity(new Intent(Intent.ACTION_MAIN).addCategory(Intent.CATEGORY_HOME).addFlags(Intent.FLAG_ACTIVITY_NEW_TASK));
    }
}
