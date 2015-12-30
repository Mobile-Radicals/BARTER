package com.trade.barter.api;

import org.json.JSONObject;

import android.app.Activity;

public class TransactionDetails {
	private String endpoint;
	private JSONObject request;
	private Activity activity;
	
	public TransactionDetails(String endpoint,JSONObject request,Activity activity)
	{
		this.endpoint=endpoint;
		this.activity=activity;
		this.request=request;
	}

	public String getEndpoint() {
		return endpoint;
	}

	public JSONObject getRequest() {
		return request;
	}

	public Activity getActivity() {
		return activity;
	}
	
	
}
