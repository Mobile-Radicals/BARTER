package com.trade.barter.utils;

/**
 * Created by Adrian on 08/08/13.
 */
public class ConsumerData {

    private int totalId;
    private String consumerId;
    private double totalSpent;
    private int totalPoints;
    private int totalTransactions;
    private String lastUpdate;

    public ConsumerData(String consumerId, double totalSpent, int totalPoints, int totalTransactions, String lastUpdate) {
        this.consumerId = consumerId;
        this.totalSpent = totalSpent;
        this.totalPoints = totalPoints;
        this.totalTransactions = totalTransactions;
        this.lastUpdate = lastUpdate;
    }

    public ConsumerData(int totalId, String consumerId, double totalSpent, int totalPoints, int totalTransactions, String lastUpdate) {
        this.totalId = totalId;
        this.consumerId = consumerId;
        this.totalSpent = totalSpent;
        this.totalPoints = totalPoints;
        this.totalTransactions = totalTransactions;
        this.lastUpdate = lastUpdate;
    }

    public int getTotalId() {
        return totalId;
    }

    public String getConsumerId() {
        return consumerId;
    }

    public double getTotalSpent() {
        return totalSpent;
    }

    public int getTotalPoints() {
        return totalPoints;
    }

    public int getTotalTransactions() {
        return totalTransactions;
    }

    public String getLastUpdate() {
        return lastUpdate;
    }

    public void setTotalId(int totalId) {
        this.totalId = totalId;
    }

    public void setConsumerId(String consumerId) {
        this.consumerId = consumerId;
    }

    public void setTotalSpent(double totalSpent) {
        this.totalSpent = totalSpent;
    }

    public void setTotalPoints(int totalPoints) {
        this.totalPoints = totalPoints;
    }

    public void setTotalTransactions(int totalTransactions) {
        this.totalTransactions = totalTransactions;
    }

    public void setLastUpdate(String lastUpdate) {
        this.lastUpdate = lastUpdate;
    }
}
