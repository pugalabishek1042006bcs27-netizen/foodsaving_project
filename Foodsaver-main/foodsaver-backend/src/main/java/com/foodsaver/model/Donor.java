package com.foodsaver.model;

import jakarta.persistence.*;
import java.time.LocalDateTime;
import lombok.*;

@Entity
@Table(name = "donor")
@Data
@NoArgsConstructor
@AllArgsConstructor
@Builder
public class Donor {

    @Id
    @GeneratedValue(strategy = GenerationType.IDENTITY)
    private Long donorId;

    private String name;

    @Column(unique = true)
    private String email;

    private String password;
    private String contactNumber;

    @Column(columnDefinition = "TEXT")
    private String address;

    private LocalDateTime createdAt = LocalDateTime.now();
    private String profilePicture;
}
