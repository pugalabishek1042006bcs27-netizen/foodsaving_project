package com.foodsaver.repository;

import com.foodsaver.model.Certificate;
import java.util.List;
import org.springframework.data.jpa.repository.JpaRepository;

public interface CertificateRepository
    extends JpaRepository<Certificate, Long>
{
    List<Certificate> findByReceiverId(Long receiverId);
    List<Certificate> findByStatus(String status);
}
