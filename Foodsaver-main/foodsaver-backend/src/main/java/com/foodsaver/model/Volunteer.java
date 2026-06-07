package com.foodsaver.model;

import jakarta.persistence.*;
import java.time.LocalDateTime;
import lombok.*;

@Entity
@Table(name = "volunteer")
@Data
@NoArgsConstructor
@AllArgsConstructor
@Builder
public class Volunteer {

    @Id
    @GeneratedValue(strategy = GenerationType.IDENTITY)
    private Long volunteerId;

    private String name;

    @Column(unique = true)
    private String email;

    private String password;
    private String contactNumber;
    private String region;
    private LocalDateTime createdAt = LocalDateTime.now();
    private String availability;
    private String profilePicture;
}
