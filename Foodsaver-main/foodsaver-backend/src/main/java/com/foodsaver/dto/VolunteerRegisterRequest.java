package com.foodsaver.dto;

import lombok.Data;

@Data
public class VolunteerRegisterRequest {

    private String name;
    private String email;
    private String password;
    private String contactNumber;
    private String region;
    private String availability;
}
