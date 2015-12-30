package com.trade.barter.api;

import java.io.BufferedReader;
import java.io.InputStream;
import java.io.InputStreamReader;

import org.apache.http.HttpEntity;
import org.apache.http.HttpResponse;
import org.apache.http.client.HttpClient;
import org.apache.http.client.methods.HttpPost;
import org.apache.http.entity.StringEntity;
import org.apache.http.impl.client.DefaultHttpClient;
import org.json.JSONObject;

import android.app.Dialog;
import android.os.AsyncTask;
import android.util.Log;
import android.widget.Toast;

import com.trade.barter.MainActivity;
import com.trade.barter.R;

public abstract class ApiTransaction extends AsyncTask<String,Void,String> {
	protected StringBuilder sb;
    protected String result;
    protected InputStream is;
    protected Dialog dialog;

    public ApiTransaction(Dialog dialog)
    {
    	this.dialog=dialog;
    }
    
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
            Log.i(MainActivity.getContext().getString(R.string.app_name), "Error connecting "+e.getMessage());
        }

        //handle the response
        try {
            BufferedReader reader = new BufferedReader(new InputStreamReader(is, "iso-8859-1"), 8);
            sb = new StringBuilder();
            sb.append(reader.readLine() + "\n");
            String line = "0";

            while ((line = reader.readLine()) != null) {
                sb.append(line + "\n");
            }
            reader.close();
            is.close();
            result = sb.toString();
            return result;
        } catch (Exception e) {
            Log.e(MainActivity.getContext().getString(R.string.app_name), "Error converting result " + e.toString());
            return null;
        }
    }
    
    @Override
    protected void onPostExecute(String result) {

        try{
            JSONObject allData = new JSONObject(result);
            Boolean received = allData.getBoolean("received");

            if(received){
                result(allData);
                dialog.dismiss();
                Toast.makeText(MainActivity.getContext().getApplicationContext(), "All data has been successfully synced.", Toast.LENGTH_LONG).show();
            }
            else{
                Log.e(MainActivity.getContext().getString(R.string.app_name), "There was a problem during the sync operation." + allData.getJSONArray("notEntered"));
                dialog.dismiss();
                Toast.makeText(MainActivity.getContext(), "There was a problem during the sync operation. Please try again later.", Toast.LENGTH_LONG).show();
            }
        }
        catch (Exception e) {
            dialog.dismiss();
            Log.e(MainActivity.getContext().getString(R.string.app_name), e.getMessage());
        }
    }
    
    abstract protected void result(JSONObject data) throws Exception;
}
