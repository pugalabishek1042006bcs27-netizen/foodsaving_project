package com.foodsaver.model;

import org.springframework.data.annotation.Id;
import org.springframework.data.mongodb.core.mapping.Document;
import java.math.BigDecimal;
import java.time.LocalDateTime;
import lombok.*;

@Document(collection = "delivery_log")
@Data
@NoArgsConstructor
@AllArgsConstructor
@Builder
public class DeliveryLog {

    @Id
    private String deliveryId;

    private String volunteerId;
    private String donationId;
    private String receiverId;
    private Boolean donorVerified = false;
    private String otpCode;
    private String pickupPhoto;
    private BigDecimal pickupLat;
    private BigDecimal pickupLng;
    private String deliveryStatus = "Assigned";
    private LocalDateTime pickedAt;
    private LocalDateTime deliveredAt;
    private LocalDateTime createdAt = LocalDateTime.now();
}
