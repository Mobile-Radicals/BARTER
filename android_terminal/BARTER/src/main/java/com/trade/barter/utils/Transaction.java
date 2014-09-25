package com.trade.barter.utils;

import java.io.Serializable;

public class Transaction implements Serializable{

	private int transactionID;
	private String traderID;
	private String consumerID;
    private double latitude;
    private double longitude;
    private String type;
    private String origin;
	private double price;
	private int points;
	private String timestamp;
	private boolean syncStatus;
	private boolean isCheckboxChecked;
	
	public Transaction(int transactionID, String traderID, String consumerID, double latitude, double longitude, String type, String origin, double price, int points, String timestamp, boolean syncStatus) {
		this.transactionID = transactionID;
		this.traderID = traderID;
		this.consumerID = consumerID;
        this.latitude = latitude;
        this.longitude = longitude;
        this.type = type;
        this.origin = origin;
		this.price = price;
        this.points = points;
		this.timestamp = timestamp;
		this.syncStatus = syncStatus;
		this.isCheckboxChecked = false;
	}

    public Transaction(String traderID, String consumerID, double latitude, double longitude, String type, String origin, double price, int points, String timestamp, boolean syncStatus){
        this.traderID = traderID;
        this.consumerID = consumerID;
        this.latitude = latitude;
        this.longitude = longitude;
        this.type = type;
        this.origin = origin;
        this.price = price;
        this.points = points;
        this.timestamp = timestamp;
        this.syncStatus = syncStatus;
        this.isCheckboxChecked = false;
    }

    public Transaction(int transactionID, String consumerID, String type, double price, int points) {
        this.transactionID = transactionID;
        this.consumerID = consumerID;
        this.type = type;
        this.price = price;
        this.points = points;
    }

    public int getTransactionID() {
        return transactionID;
    }

    public String getTraderID() {
        return traderID;
    }

    public String getConsumerID() {
        return consumerID;
    }

    public double getLatitude() {
        return latitude;
    }

    public double getLongitude() {
        return longitude;
    }

    public String getType() {
        return type;
    }

    public String getOrigin() {
        return origin;
    }

    public double getPrice() {
        return price;
    }

    public int getPoints() {
        return points;
    }

    public String getTimestamp() {
        return timestamp;
    }

    public boolean isSyncStatus() {
        return syncStatus;
    }

    public boolean isCheckboxChecked() {
        return isCheckboxChecked;
    }

    public void setTransactionID(int transactionID) {
        this.transactionID = transactionID;
    }

    public void setTraderID(String traderID) {
        this.traderID = traderID;
    }

    public void setConsumerID(String consumerID) {
        this.consumerID = consumerID;
    }

    public void setLatitude(double latitude) {
        this.latitude = latitude;
    }

    public void setLongitude(double longitude) {
        this.longitude = longitude;
    }

    public void setType(String type) {
        this.type = type;
    }

    public void setOrigin(String origin) {
        this.origin = origin;
    }

    public void setPrice(double price) {
        this.price = price;
    }

    public void setPoints(int points) {
        this.points = points;
    }

    public void setTimestamp(String timestamp) {
        this.timestamp = timestamp;
    }

    public void setSyncStatus(boolean syncStatus) {
        this.syncStatus = syncStatus;
    }

    public void setCheckboxChecked(boolean checkboxChecked) {
        isCheckboxChecked = checkboxChecked;
    }
}
