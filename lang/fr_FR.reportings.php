<?php
/* Lozeil -- Copyright (C) No Parking 2014 - 2014 */

$reportings_plan = array(
	'multiple_default_plan_first' => array(
		'Marges brutes' => array(
			'base' => true,
			'A' => "chiffres d'affaires",
			'B' => "charges directes"
		),
		'Marges sur coût productif' => array(
			'C' => "coût productif"
		)
	),

	'multiple_default_plan_other' => array(
		'Marges brutes' => array(
			'base' => true,
			'D' => "chiffres d'affaires",
			'E' => "charges directes"
		),
		'Marges sur coût productif' => array(
			'F' => "coût productif"
		),
	),

	'multiple_default_plan_global' => array(
		'Résultat d\'exploitation' => array(
			'G' => "autres produits",
			'H' => "frais généraux",
			'I' => "impôts et taxes",
			'J' => "dotation amortissement et provisions"
		),
		'Résultat' => array(
			'K' => "charges financières",
			'L' => "produits financiers",
			'M' => "charges exceptionnelles",
			'N' => "produits exceptionnels",
			'O' => "impôt sociétés"
		)
	),

	'single_default_plan' => array(
		'Marges brutes' => array(
			'base' => true,
			'A' => "chiffres d'affaires",
			'B' => "charges directes"
		),
		'Marges sur coût productif' => array(
			'C' => "coût productif"
		),
		'Résultat d\'exploitation' => array(
			'G' => "autres produits",
			'H' => "frais généraux",
			'I' => "impôts et taxes",
			'J' => "dotation amortissement et provisions"
		),
		'Résultat' => array(
			'K' => "charges financières",
			'L' => "produits financiers",
			'M' => "charges exceptionnelles",
			'N' => "produits exceptionnels",
			'O' => "impôt sociétés"
		)
	),
);
