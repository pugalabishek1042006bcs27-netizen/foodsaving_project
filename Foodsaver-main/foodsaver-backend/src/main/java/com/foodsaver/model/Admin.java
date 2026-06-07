package com.foodsaver.model;

import java.time.LocalDateTime;
import lombok.*;
import org.springframework.data.annotation.Id;
import org.springframework.data.mongodb.core.index.Indexed;
import org.springframework.data.mongodb.core.mapping.Document;

@Document(collection = "admin")
@Data
@NoArgsConstructor
@AllArgsConstructor
@Builder
public class Admin {

    @Id
    private String adminId;

    private String name;

    @Indexed(unique = true)
    private String email;

    private String password;
    private LocalDateTime createdAt = LocalDateTime.now();
}
