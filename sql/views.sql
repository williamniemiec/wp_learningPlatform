-- ----------------------------------------------------------------------------
-- 		Visões
-- ----------------------------------------------------------------------------
-- Total de minutos que os curso possuem.
-- ----------------------------------------------------------------------------
CREATE VIEW vw_courses_total_length (id_course, total_length) AS (
	SELECT		id_course, SUM(length) as total_length
	FROM 		(SELECT	id_module, length
		 		 FROM		videos
		 	 	 UNION ALL
		  		 SELECT		id_module, 5 AS length
		  		 FROM		questionnaires) AS classes
	JOIN 	 	 course_modules USING (id_module)
	GROUP BY 	 id_course
);

-- ----------------------------------------------------------------------------
-- Total de minutos assistidos das aulas pelos estudantes.
-- ----------------------------------------------------------------------------
CREATE VIEW vw_student_historic_watched_length (id_student, id_module, length) AS (
	SELECT	id_student, id_module, length
	FROM	(SELECT	id_module, class_order, length
	 		 FROM		videos
	 	 	 UNION ALL
	  		 SELECT		id_module, class_order, 5 AS length
	  		 FROM		questionnaires) AS classes
	JOIN student_historic USING (id_module, class_order)
);
