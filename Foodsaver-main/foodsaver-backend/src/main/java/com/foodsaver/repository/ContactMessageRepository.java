package com.foodsaver.repository;

import com.foodsaver.model.ContactMessage;
import org.springframework.data.jpa.repository.JpaRepository;

public interface ContactMessageRepository
    extends JpaRepository<ContactMessage, Long> {}
