<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;

class ProductSeeder extends Seeder
{
    public function run()
    {
        $products = [
            // ===== NETTOYANTS =====
            [
                "id" => 1,
                "name" => "Savon Visage Doux Victoire",
                "price" => 3500,
                "category" => "Beauté & Soins",
                "rating" => 5,
                "img" => "/images/products/serum-vitamine-c.jpg",
                "description" => "Savon surgras pour nettoyer la peau sans l'agresser, adapté à tous les types de peau.",
                "features" => ["Nettoie en douceur", "Respecte le pH", "Sans savon"],
                "skin_type_tag" => "tous",
                "problem_tag" => "tous",
                "routine_step" => "nettoyant",
                "is_victoire" => true
            ],
            [
                "id" => 2,
                "name" => "Gel Moussant Purifiant",
                "price" => 4500,
                "category" => "Beauté & Soins",
                "rating" => 4,
                "img" => "/images/products/huile-naturelle.jpg",
                "description" => "Gel nettoyant pour peaux grasses à tendance acnéique.",
                "features" => ["Purifiant", "Régule le sébum", "Non desséchant"],
                "skin_type_tag" => "grasse",
                "problem_tag" => "acne",
                "routine_step" => "nettoyant",
                "is_victoire" => true
            ],
            // ===== SÉRUMS =====
            [
                "id" => 3,
                "name" => "Sérum Éclat Vitamine C",
                "price" => 12500,
                "category" => "Beauté & Soins",
                "rating" => 5,
                "img" => "/images/products/serum-vitamine-c.jpg",
                "description" => "Un sérum puissant pour illuminer votre teint et réduire les signes de fatigue.",
                "features" => ["Éclat immédiat", "Antioxydant puissant", "Hydratation profonde"],
                "skin_type_tag" => "tous",
                "problem_tag" => "taches",
                "routine_step" => "serum",
                "is_victoire" => false
            ],
            [
                "id" => 4,
                "name" => "Sérum Anti-Imperfections",
                "price" => 9500,
                "category" => "Beauté & Soins",
                "rating" => 4,
                "img" => "/images/products/serum-vitamine-c.jpg",
                "description" => "Sérum ciblé pour réduire les boutons et les marques d'acné.",
                "features" => ["Anti-bactérien", "Répare les marques", "Non comédogène"],
                "skin_type_tag" => "grasse",
                "problem_tag" => "acne",
                "routine_step" => "serum",
                "is_victoire" => true
            ],
            // ===== HYDRATANTS =====
            [
                "id" => 5,
                "name" => "Crème Hydratation Intense",
                "price" => 8500,
                "category" => "Beauté & Soins",
                "rating" => 5,
                "img" => "/images/products/huile-naturelle.jpg",
                "description" => "Crème riche pour les peaux sèches et déshydratées.",
                "features" => ["Hydratation 24h", "Riche en actifs", "Peaux sensibles"],
                "skin_type_tag" => "seche",
                "problem_tag" => "secheresse",
                "routine_step" => "creme",
                "is_victoire" => true
            ],
            [
                "id" => 6,
                "name" => "Crème Légère Matifiante",
                "price" => 7500,
                "category" => "Beauté & Soins",
                "rating" => 4,
                "img" => "/images/products/huile-naturelle.jpg",
                "description" => "Crème légère hydratante qui contrôle la brillance sans assécher.",
                "features" => ["Effet mat", "Contrôle sébum", "Texture légère"],
                "skin_type_tag" => "grasse",
                "problem_tag" => "tous",
                "routine_step" => "creme",
                "is_victoire" => true
            ],
            // ===== SOINS SPÉCIFIQUES =====
            [
                "id" => 7,
                "name" => "Huile d'Aygue Pure",
                "price" => 8000,
                "category" => "Huiles Naturelles",
                "rating" => 4,
                "img" => "/images/products/huile-naturelle.jpg",
                "description" => "Huile 100% naturelle pour nourrir votre peau et vos cheveux en profondeur.",
                "features" => ["100% Bio", "Multi-usages", "Nourrissant"],
                "skin_type_tag" => "seche",
                "problem_tag" => "secheresse",
                "routine_step" => "corps",
                "is_victoire" => false
            ],
            [
                "id" => 8,
                "name" => "Gommage Visage Doux Victoire",
                "price" => 5000,
                "category" => "Beauté & Soins",
                "rating" => 5,
                "img" => "/images/products/serum-vitamine-c.jpg",
                "description" => "Gommage enzymatique doux pour éliminer les cellules mortes et illuminer le teint.",
                "features" => ["Exfoliation douce", "Teint lumineux", "Texture fine"],
                "skin_type_tag" => "tous",
                "problem_tag" => "terne",
                "routine_step" => "gommage",
                "is_victoire" => true
            ],
            [
                "id" => 9,
                "name" => "Masque Détox Argile Victoire",
                "price" => 5500,
                "category" => "Beauté & Soins",
                "rating" => 4,
                "img" => "/images/products/serum-vitamine-c.jpg",
                "description" => "Masque à l'argile verte purifiant pour nettoyer les pores et absorber l'excès de sébum.",
                "features" => ["Purifiant", "Resserre les pores", "Matifiant"],
                "skin_type_tag" => "grasse",
                "problem_tag" => "acne",
                "routine_step" => "masque",
                "is_victoire" => true
            ],
            [
                "id" => 10,
                "name" => "Lait Corps Nourrissant Victoire",
                "price" => 6000,
                "category" => "Huiles Naturelles",
                "rating" => 5,
                "img" => "/images/products/huile-naturelle.jpg",
                "description" => "Lait corporel hydratant pour nourrir et adoucir la peau du corps en profondeur.",
                "features" => ["Hydratation longue durée", "Pénétration rapide", "Senteur douce"],
                "skin_type_tag" => "seche",
                "problem_tag" => "secheresse",
                "routine_step" => "corps",
                "is_victoire" => true
            ],
            [
                "id" => 11,
                "name" => "Soin Contour des Yeux Victoire",
                "price" => 7000,
                "category" => "Beauté & Soins",
                "rating" => 4,
                "img" => "/images/products/serum-vitamine-c.jpg",
                "description" => "Soin spécifique pour atténuer les rides et les poches sous les yeux.",
                "features" => ["Anti-rides", "Décongestionnant", "Texture fraîche"],
                "skin_type_tag" => "tous",
                "problem_tag" => "rides",
                "routine_step" => "creme",
                "is_victoire" => true
            ],
            [
                "id" => 12,
                "name" => "Protection Solaire SPF50+ Victoire",
                "price" => 8500,
                "category" => "Beauté & Soins",
                "rating" => 5,
                "img" => "/images/products/serum-vitamine-c.jpg",
                "description" => "Protection solaire haute protection pour prévenir les taches et le vieillissement cutané.",
                "features" => ["SPF50+", "Prévient les taches", "Texture invisible"],
                "skin_type_tag" => "tous",
                "problem_tag" => "taches",
                "routine_step" => "soleil",
                "is_victoire" => true
            ],
            [
                "id" => 13,
                "name" => "Thé Détox Wahoo",
                "price" => 4500,
                "category" => "Thés & Infusions",
                "rating" => 5,
                "img" => "/images/products/detox-wahoo.jpg",
                "description" => "Une cure détox savoureuse pour purifier votre organisme.",
                "features" => ["Purifiant", "Digestion facile", "Naturel"],
                "skin_type_tag" => "tous",
                "problem_tag" => "terne",
                "routine_step" => "corps",
                "is_victoire" => false
            ],
            [
                "id" => 14,
                "name" => "Lait Corps Bébé Bio",
                "price" => 6500,
                "category" => "Produits Bébé",
                "rating" => 5,
                "img" => "/images/products/produit-bebe.jpg",
                "description" => "Douceur extrême pour la peau délicate de votre bébé.",
                "features" => ["Hypoallergénique", "Testé sous contrôle pédiatrique", "Sans parfum"],
                "skin_type_tag" => "normale",
                "problem_tag" => "tous",
                "routine_step" => "corps",
                "is_victoire" => false
            ],
        ];

        foreach ($products as $product) {
            Product::updateOrCreate(['id' => $product['id']], $product);
        }
    }
}
