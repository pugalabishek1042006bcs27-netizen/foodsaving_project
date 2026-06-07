package com.foodsaver.model;

import jakarta.persistence.*;
import java.time.LocalDateTime;
import lombok.*;

@Entity
@Table(name = "food_requests")
@Data
@NoArgsConstructor
@AllArgsConstructor
@Builder
public class FoodRequest {

    @Id
    @GeneratedValue(strategy = GenerationType.IDENTITY)
    private Long requestId;

    private Long receiverId;
    private Long donationId;
    private Integer quantity = 1;

    @Column(columnDefinition = "TEXT")
    private String details;

    private LocalDateTime requestDate = LocalDateTime.now();
    private String status = "Pending";
    private Long volunteerId;
    private String receiverOtp;
}
