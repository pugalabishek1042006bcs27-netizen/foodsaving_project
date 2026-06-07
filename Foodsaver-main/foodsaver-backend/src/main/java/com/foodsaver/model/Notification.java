package com.foodsaver.model;

import java.time.LocalDateTime;
import lombok.*;
import org.springframework.data.annotation.Id;
import org.springframework.data.mongodb.core.mapping.Document;

@Document(collection = "notifications")
@Data
@NoArgsConstructor
@AllArgsConstructor
@Builder
public class Notification {

    @Id
    private String notificationId;

    private String userType;
    private String userId;

    private String message;

    private LocalDateTime createdAt = LocalDateTime.now();
}
