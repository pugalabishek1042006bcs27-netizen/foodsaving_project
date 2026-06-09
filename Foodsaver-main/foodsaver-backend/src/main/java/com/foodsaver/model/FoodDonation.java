package com.foodsaver.model;

import org.springframework.data.annotation.Id;
import org.springframework.data.mongodb.core.mapping.Document;
import java.time.LocalDate;
import java.time.LocalDateTime;
import lombok.*;

@Document(collection = "food_donations")
@Data
@NoArgsConstructor
@AllArgsConstructor
@Builder
public class FoodDonation {

    @Id
    private String donationId;

    private String donorId;
    private String foodType;
    private String dietaryOptions;
    private String allergens;
    private String description;
    private String quantity;
    private LocalDate expiryDate;
    private String address;
    private String imagePaths;
    private String contactPhone;
    private String contactEmail;
    private String status = "pending";
    private String preparationStatus;
    private String volunteerId;
    private String otpCode;
    private LocalDateTime uploadDate = LocalDateTime.now();
}
