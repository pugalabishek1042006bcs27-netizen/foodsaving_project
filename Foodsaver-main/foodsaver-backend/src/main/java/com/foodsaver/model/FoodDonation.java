package com.foodsaver.model;

import jakarta.persistence.*;
import java.time.LocalDate;
import java.time.LocalDateTime;
import lombok.*;

@Entity
@Table(name = "food_donations")
@Data
@NoArgsConstructor
@AllArgsConstructor
@Builder
public class FoodDonation {

    @Id
    @GeneratedValue(strategy = GenerationType.IDENTITY)
    private Long donationId;

    private Long donorId;
    private String foodType;
    private String dietaryOptions;
    private String allergens;

    @Column(columnDefinition = "TEXT")
    private String description;

    private String quantity;
    private LocalDate expiryDate;

    @Column(columnDefinition = "TEXT")
    private String address;

    @Column(columnDefinition = "TEXT")
    private String imagePaths;

    private String contactPhone;
    private String contactEmail;
    private String status = "pending";
    private String preparationStatus;
    private Long volunteerId;
    private String otpCode;
    private LocalDateTime uploadDate = LocalDateTime.now();
}
