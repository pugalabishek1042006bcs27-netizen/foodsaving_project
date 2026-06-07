package com.foodsaver.dto;

import lombok.Data;

@Data
public class DonorRegisterRequest {

    private String name;
    private String email;
    private String password;
    private String contactNumber;
    private String address;
}
