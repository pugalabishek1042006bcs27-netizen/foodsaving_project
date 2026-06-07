package com.foodsaver.model;

import jakarta.persistence.*;
import java.math.BigDecimal;
import java.time.LocalDateTime;
import lombok.*;

@Entity
@Table(name = "delivery_log")
@Data
@NoArgsConstructor
@AllArgsConstructor
@Builder
public class DeliveryLog {

    @Id
    @GeneratedValue(strategy = GenerationType.IDENTITY)
    private Long deliveryId;

    private Long volunteerId;
    private Long donationId;
    private Long receiverId;
    private Boolean donorVerified = false;
    private String otpCode;

    @Column(columnDefinition = "TEXT")
    private String pickupPhoto;

    private BigDecimal pickupLat;
    private BigDecimal pickupLng;
    private String deliveryStatus = "Assigned";
    private LocalDateTime pickedAt;
    private LocalDateTime deliveredAt;
    private LocalDateTime createdAt = LocalDateTime.now();
}
