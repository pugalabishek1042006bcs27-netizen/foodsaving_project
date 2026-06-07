package com.foodsaver.model;

import jakarta.persistence.*;
import java.time.LocalDateTime;
import lombok.*;

@Entity
@Table(name = "receivers")
@Data
@NoArgsConstructor
@AllArgsConstructor
@Builder
public class Receiver {

    @Id
    @GeneratedValue(strategy = GenerationType.IDENTITY)
    private Long receiverId;

    private String orgName;
    private String receiverName;
    private String phone;

    @Column(unique = true)
    private String email;

    private String password;

    @Column(columnDefinition = "TEXT")
    private String address;

    private String city;
    private String state;
    private String pincode;
    private LocalDateTime registrationDate = LocalDateTime.now();
    private String profilePicture;
}
