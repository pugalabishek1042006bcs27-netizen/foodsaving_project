package com.foodsaver.dto;

import lombok.Data;

@Data
public class ReceiverRegisterRequest {

    private String orgName;
    private String receiverName;
    private String phone;
    private String email;
    private String password;
    private String address;
    private String city;
    private String state;
    private String pincode;
}
