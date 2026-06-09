package com.foodsaver.model;

import org.springframework.data.annotation.Id;
import org.springframework.data.mongodb.core.index.Indexed;
import org.springframework.data.mongodb.core.mapping.Document;
import java.time.LocalDateTime;
import lombok.*;

@Document(collection = "donor")
@Data
@NoArgsConstructor
@AllArgsConstructor
@Builder
public class Donor {

    @Id
    private String donorId;

    private String name;

    @Indexed(unique = true)
    private String email;

    private String password;
    private String contactNumber;
    private String address;
    private LocalDateTime createdAt = LocalDateTime.now();
    private String profilePicture;
}
