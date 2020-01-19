ALTER TABLE `__prefix__RollGroup`
    ADD CONSTRAINT FOREIGN KEY (`academic_year`) REFERENCES `__prefix__AcademicYear` (`id`),
    ADD CONSTRAINT FOREIGN KEY (`tutor1`) REFERENCES `__prefix__Person` (`id`),
    ADD CONSTRAINT FOREIGN KEY (`tutor2`) REFERENCES `__prefix__Person` (`id`),
    ADD CONSTRAINT FOREIGN KEY (`tutor3`) REFERENCES `__prefix__Person` (`id`),
    ADD CONSTRAINT FOREIGN KEY (`assistant1`) REFERENCES `__prefix__Person` (id),
    ADD CONSTRAINT FOREIGN KEY (`assistant2`) REFERENCES `__prefix__Person` (`id`),
    ADD CONSTRAINT FOREIGN KEY (`assistant3`) REFERENCES `__prefix__Person` (id),
    ADD CONSTRAINT FOREIGN KEY (`facility`) REFERENCES `__prefix__Facility` (`id`),
    ADD CONSTRAINT FOREIGN KEY (`next_roll_group`) REFERENCES `__prefix__RollGroup` (`id`);
