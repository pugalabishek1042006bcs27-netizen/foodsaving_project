package com.foodsaver.model;

import jakarta.persistence.*;
import java.time.LocalDateTime;
import lombok.*;

@Entity
@Table(name = "certificates")
@Data
@NoArgsConstructor
@AllArgsConstructor
@Builder
public class Certificate {

    @Id
    @GeneratedValue(strategy = GenerationType.IDENTITY)
    private Long certId;

    private Long receiverId;
    private String filePath;
    private String status = "Pending";
    private LocalDateTime uploadedAt = LocalDateTime.now();
}
