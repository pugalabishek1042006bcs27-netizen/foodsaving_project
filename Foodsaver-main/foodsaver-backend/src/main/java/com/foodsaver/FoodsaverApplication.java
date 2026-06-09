package com.foodsaver;

import com.foodsaver.model.Admin;
import com.foodsaver.repository.AdminRepository;
import jakarta.annotation.PostConstruct;
import org.slf4j.Logger;
import org.slf4j.LoggerFactory;
import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.boot.SpringApplication;
import org.springframework.boot.autoconfigure.SpringBootApplication;
import org.springframework.security.crypto.password.PasswordEncoder;

@SpringBootApplication
public class FoodsaverApplication {

    private static final Logger log = LoggerFactory.getLogger(FoodsaverApplication.class);

    @Autowired
    private AdminRepository adminRepo;

    @Autowired
    private PasswordEncoder passwordEncoder;

    public static void main(String[] args) {
        SpringApplication.run(FoodsaverApplication.class, args);
    }

    @PostConstruct
    public void seedAdmin() {
        if (adminRepo.findByEmail("admin@foodsaver.com").isEmpty()) {
            Admin admin = Admin.builder()
                .name("Admin")
                .email("admin@foodsaver.com")
                .password(passwordEncoder.encode("admin123"))
                .build();
            adminRepo.save(admin);
            log.info("Default admin created: admin@foodsaver.com / admin123");
        }
    }
}
