package com.foodsaver.model;

import org.springframework.data.annotation.Id;
import org.springframework.data.mongodb.core.mapping.Document;
import java.time.LocalDateTime;
import lombok.*;

@Document(collection = "food_requests")
@Data
@NoArgsConstructor
@AllArgsConstructor
@Builder
public class FoodRequest {

    @Id
    private String requestId;

    private String receiverId;
    private String donationId;
    private Integer quantity = 1;
    private String details;
    private LocalDateTime requestDate = LocalDateTime.now();
    private String status = "Pending";
    private String volunteerId;
    private String receiverOtp;
}
