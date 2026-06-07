package com.foodsaver.model;

import java.time.LocalDateTime;
import lombok.*;
import org.springframework.data.annotation.Id;
import org.springframework.data.mongodb.core.index.Indexed;
import org.springframework.data.mongodb.core.mapping.Document;

@Document(collection = "receivers")
@Data
@NoArgsConstructor
@AllArgsConstructor
@Builder
public class Receiver {

    @Id
    private String receiverId;

    private String orgName;
    private String receiverName;
    private String phone;

    @Indexed(unique = true)
    private String email;

    private String password;

    private String address;

    private String city;
    private String state;
    private String pincode;
    private LocalDateTime registrationDate = LocalDateTime.now();
    private String profilePicture;
}
