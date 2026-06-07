package com.foodsaver.repository;

import com.foodsaver.model.Receiver;
import java.util.Optional;
import org.springframework.data.jpa.repository.JpaRepository;

public interface ReceiverRepository extends JpaRepository<Receiver, Long> {
    Optional<Receiver> findByEmail(String email);
    boolean existsByEmail(String email);
}
