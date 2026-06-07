package com.foodsaver.model;

import java.time.LocalDateTime;
import lombok.*;
import org.springframework.data.annotation.Id;
import org.springframework.data.mongodb.core.mapping.Document;

@Document(collection = "contact_messages")
@Data
@NoArgsConstructor
@AllArgsConstructor
@Builder
public class ContactMessage {

    @Id
    private String id;

    private String name;
    private String email;
    private String message;

    private LocalDateTime createdAt = LocalDateTime.now();
}
