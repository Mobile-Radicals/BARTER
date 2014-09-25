package com.trade.barter.utils;

import java.io.Serializable;

/**
 * Created by adriangradinar on 30/10/2013.
 */
public class Redeem implements Serializable {

    private int redeemID;
    private String traderID;
    private String consumerRFID;
    private String redeemType;
    private int pointsDeducted;
    private String timestamp;
    private boolean isSynced;

    public Redeem(int redeemID, String traderID, String consumerRFID, String redeemType, int pointsDeducted, String timestamp, boolean isSynced) {
        this.redeemID = redeemID;
        this.traderID = traderID;
        this.consumerRFID = consumerRFID;
        this.redeemType = redeemType;
        this.pointsDeducted = pointsDeducted;
        this.timestamp = timestamp;
        this.isSynced = isSynced;
    }

    public Redeem(String traderID, String consumerRFID, String redeemType, int pointsDeducted, String timestamp, boolean isSynced) {
        this.traderID = traderID;
        this.consumerRFID = consumerRFID;
        this.redeemType = redeemType;
        this.pointsDeducted = pointsDeducted;
        this.timestamp = timestamp;
        this.isSynced = isSynced;
    }

    public int getRedeemID() {
        return redeemID;
    }

    public void setRedeemID(int redeemID) {
        this.redeemID = redeemID;
    }

    public String getTraderID() {
        return traderID;
    }

    public void setTraderID(String traderID) {
        this.traderID = traderID;
    }

    public String getConsumerRFID() {
        return consumerRFID;
    }

    public void setConsumerRFID(String consumerRFID) {
        this.consumerRFID = consumerRFID;
    }

    public String getRedeemType() {
        return redeemType;
    }

    public void setRedeemType(String redeemType) {
        this.redeemType = redeemType;
    }

    public int getPointsDeducted() {
        return pointsDeducted;
    }

    public void setPointsDeducted(int pointsDeducted) {
        this.pointsDeducted = pointsDeducted;
    }

    public String getTimestamp() {
        return timestamp;
    }

    public void setTimestamp(String timestamp) {
        this.timestamp = timestamp;
    }

    public boolean isSynced() {
        return isSynced;
    }

    public void setSynced(boolean isSynced) {
        this.isSynced = isSynced;
    }
}
