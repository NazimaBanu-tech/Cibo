USE cibo_db;

START TRANSACTION;

INSERT INTO restaurants (
  name,
  slug,
  image,
  hero_image,
  category,
  cuisine,
  location,
  address,
  rating,
  delivery_time,
  offer_text,
  is_active
) VALUES
  ('McDonald''s', 'mcdonalds', 'images/restaurants/mcd.jpg', 'images/restaurants/mcd.jpg', 'burgers', 'Burgers, Fast Food', 'JP Nagar', 'JP Nagar, Bangalore', 4.3, '30-35 mins', 'Free delivery on orders above Rs199', 1),
  ('Burger King', 'burger-king', 'images/restaurants/burger-king.jpg', 'images/restaurants/burger-king.jpg', 'burgers', 'Burgers, American', 'BTM Layout', 'BTM Layout, Bangalore', 4.2, '25-30 mins', 'Free delivery on orders above Rs199', 1),
  ('Domino''s', 'dominos', 'images/restaurants/dominos.jpg', 'images/restaurants/dominos.jpg', 'pizza', 'Pizza, Italian', 'Jayanagar', 'Jayanagar, Bangalore', 4.2, '25-30 mins', 'Free delivery on orders above Rs199', 1),
  ('Pizza Hut', 'pizza-hut', 'images/restaurants/pizza-hut.jpg', 'images/restaurants/pizza-hut.jpg', 'pizza', 'Pizza, Italian', 'Banashankari', 'Banashankari, Bangalore', 4.2, '25-30 mins', 'Free delivery on orders above Rs199', 1),
  ('Meghana Foods', 'meghana', 'images/restaurants/meghana.jpg', 'images/restaurants/meghana.jpg', 'biryani', 'Biryani, Andhra', 'Koramangala', 'Koramangala, Bangalore', 4.5, '30-40 mins', 'Free delivery on orders above Rs249', 1),
  ('Paradise', 'paradise', 'images/restaurants/paradise.jpg', 'images/restaurants/paradise.jpg', 'biryani', 'Biryani, Hyderabadi', 'MG Road', 'MG Road, Bangalore', 4.3, '30-40 mins', 'Free delivery on orders above Rs249', 1),
  ('Chinese Wok', 'chinese-wok', 'images/restaurants/chinese-wok.jpg', 'images/restaurants/chinese-wok.jpg', 'chinese', 'Chinese, Noodles', 'HSR Layout', 'HSR Layout, Bangalore', 4.1, '25-30 mins', 'Free delivery on orders above Rs199', 1),
  ('Mainland China', 'mainland-china', 'images/restaurants/mainland-china.jpg', 'images/restaurants/mainland-china.jpg', 'chinese', 'Chinese, Asian', 'Indiranagar', 'Indiranagar, Bangalore', 4.4, '35-40 mins', 'Free delivery on orders above Rs249', 1),
  ('Empire', 'empire', 'images/restaurants/empire.jpg', 'images/restaurants/empire.jpg', 'north indian', 'Grill, North Indian', 'Rajajinagar', 'Rajajinagar, Bangalore', 4.2, '30-35 mins', 'Free delivery on orders above Rs199', 1),
  ('Punjab Grill', 'punjab-grill', 'images/restaurants/punjab-grill.jpg', 'images/restaurants/punjab-grill.jpg', 'north indian', 'North Indian, Punjabi', 'Malleshwaram', 'Malleshwaram, Bangalore', 4.3, '35-40 mins', 'Free delivery on orders above Rs249', 1),
  ('Udupi', 'udupi', 'images/restaurants/udupi.jpg', 'images/restaurants/udupi.jpg', 'south indian', 'South Indian, Breakfast', 'Basavanagudi', 'Basavanagudi, Bangalore', 4.4, '20-25 mins', 'Free delivery on orders above Rs149', 1),
  ('Vidyarthi Bhavan', 'vidyarthi', 'images/restaurants/vidyarthi.jpg', 'images/restaurants/vidyarthi.jpg', 'south indian', 'Dosa, South Indian', 'Basavanagudi', 'Basavanagudi, Bangalore', 4.6, '20-25 mins', 'Free delivery on orders above Rs149', 1),
  ('Polar Bear', 'polar-bear', 'images/restaurants/polar-bear.jpg', 'images/restaurants/polar-bear.jpg', 'desserts', 'Ice Cream, Desserts', 'Jayanagar', 'Jayanagar, Bangalore', 4.4, '20-25 mins', 'Free delivery on orders above Rs149', 1),
  ('Corner House', 'corner-house', 'images/restaurants/corner-house.jpg', 'images/restaurants/corner-house.jpg', 'desserts', 'Desserts, Sundaes', 'Indiranagar', 'Indiranagar, Bangalore', 4.5, '20-25 mins', 'Free delivery on orders above Rs149', 1),
  ('FreshMenu', 'freshmenu', 'images/restaurants/freshmenu.jpg', 'images/restaurants/freshmenu.jpg', 'salad', 'Healthy, Continental', 'Bellandur', 'Bellandur, Bangalore', 4.2, '25-30 mins', 'Free delivery on orders above Rs199', 1),
  ('EatFit', 'eatfit', 'images/restaurants/eatfit.jpg', 'images/restaurants/eatfit.jpg', 'salad', 'Healthy Food, Fitness Meals', 'HSR Layout', 'HSR Layout, Bangalore', 4.4, '25-30 mins', 'Free delivery on orders above Rs199', 1),
  ('Hae Kum Gang', 'hae-kum-gang', 'images/restaurants/hae-kum-gang.jpg', 'images/restaurants/hae-kum-gang.jpg', 'korean', 'Korean, Asian', 'Koramangala', 'Koramangala, Bangalore', 4.4, '30-35 mins', 'Free delivery on orders above Rs249', 1)
ON DUPLICATE KEY UPDATE
  name = VALUES(name),
  image = VALUES(image),
  hero_image = VALUES(hero_image),
  category = VALUES(category),
  cuisine = VALUES(cuisine),
  location = VALUES(location),
  address = VALUES(address),
  rating = VALUES(rating),
  delivery_time = VALUES(delivery_time),
  offer_text = VALUES(offer_text),
  is_active = VALUES(is_active);

DELETE FROM menu_items
WHERE restaurant_id IN (
  SELECT id
  FROM restaurants
  WHERE slug IN (
    'mcdonalds',
    'burger-king',
    'dominos',
    'pizza-hut',
    'meghana',
    'paradise',
    'chinese-wok',
    'mainland-china',
    'empire',
    'punjab-grill',
    'udupi',
    'vidyarthi',
    'polar-bear',
    'corner-house',
    'freshmenu',
    'eatfit',
    'hae-kum-gang'
  )
);

INSERT INTO menu_items (
  restaurant_id,
  name,
  slug,
  price,
  image,
  description,
  food_type,
  is_available
)
SELECT
  r.id,
  seed.name,
  seed.slug,
  seed.price,
  seed.image,
  seed.description,
  seed.food_type,
  1
FROM restaurants r
JOIN (
  SELECT 'mcdonalds' AS restaurant_slug, 'Chicken Burger' AS name, 'chicken-burger' AS slug, 139.00 AS price, 'images/food-items/mcdonalds/burger.jpg' AS image, 'Juicy chicken patty with cheese, lettuce and a soft burger bun.' AS description, 'nonveg' AS food_type
  UNION ALL SELECT 'mcdonalds', 'McAloo Tikki', 'mc-aloo-tikki', 79.00, 'images/food-items/mcdonalds/mcaloo.jpg', 'Classic crispy aloo patty burger with fresh onions and sauce.', 'veg'
  UNION ALL SELECT 'mcdonalds', 'French Fries', 'french-fries', 99.00, 'images/food-items/mcdonalds/fries.jpg', 'Crispy golden fries with light seasoning, perfect as a side.', 'veg'
  UNION ALL SELECT 'mcdonalds', 'Chicken Nuggets', 'chicken-nuggets', 159.00, 'images/food-items/mcdonalds/nuggets.jpg', 'Crunchy chicken nuggets served with a tasty dip.', 'nonveg'
  UNION ALL SELECT 'mcdonalds', 'Chicken Wrap', 'chicken-wrap', 129.00, 'images/food-items/mcdonalds/wrap.jpg', 'Soft wrap filled with chicken, sauce and fresh vegetables.', 'nonveg'
  UNION ALL SELECT 'mcdonalds', 'Coke', 'coke', 60.00, 'images/food-items/mcdonalds/coke.jpg', 'Refreshing chilled coke to pair with your meal.', 'none'
  UNION ALL SELECT 'mcdonalds', 'McFlurry', 'mcflurry', 119.00, 'images/food-items/mcdonalds/mcflurry.jpg', 'Creamy dessert topped with delicious chocolate mix.', 'veg'
  UNION ALL SELECT 'mcdonalds', 'Burger Combo', 'burger-combo', 199.00, 'images/food-items/mcdonalds/combo.jpg', 'Chicken burger served with fries and a chilled drink.', 'nonveg'

  UNION ALL SELECT 'burger-king', 'Whopper Burger', 'whopper-burger', 219.00, 'images/food-items/burger-king/whopper-burger.jpg', 'Flame-grilled burger with fresh veggies and signature sauces.', 'nonveg'
  UNION ALL SELECT 'burger-king', 'Crispy Veg Burger', 'crispy-veg-burger', 169.00, 'images/food-items/burger-king/crispy-veg-burger.jpg', 'Crunchy veg patty with lettuce and creamy sauce.', 'veg'
  UNION ALL SELECT 'burger-king', 'Chicken Royale Burger', 'chicken-royale-burger', 229.00, 'images/food-items/burger-king/chicken-royale-burger.jpg', 'Juicy chicken patty burger with soft bun and mayo.', 'nonveg'
  UNION ALL SELECT 'burger-king', 'Peri Peri Fries', 'peri-peri-fries', 119.00, 'images/food-items/burger-king/peri-peri-fries.jpg', 'Crispy fries tossed in spicy peri peri seasoning.', 'veg'
  UNION ALL SELECT 'burger-king', 'Cheesy Loaded Fries', 'cheesy-loaded-fries', 149.00, 'images/food-items/burger-king/cheesy-loaded-fries.jpg', 'Loaded fries with creamy cheese topping.', 'veg'
  UNION ALL SELECT 'burger-king', 'Veggie Nuggets', 'veggie-nuggets', 139.00, 'images/food-items/burger-king/veggie-nuggets.jpg', 'Crispy bite-sized nuggets perfect as a snack.', 'veg'
  UNION ALL SELECT 'burger-king', 'Chocolate Sundae', 'chocolate-sundae', 99.00, 'images/food-items/burger-king/chocolate-sundae.jpg', 'Creamy dessert topped with chocolate syrup.', 'veg'
  UNION ALL SELECT 'burger-king', 'Mojito Lime Cooler', 'mojito-lime-cooler', 89.00, 'images/food-items/burger-king/mojito-lime-cooler.jpg', 'Refreshing minty lime cooler drink.', 'none'

  UNION ALL SELECT 'dominos', 'Margherita Pizza', 'margherita-pizza', 199.00, 'images/food-items/dominos/margherita.jpg', 'Classic cheese pizza with rich tomato sauce.', 'veg'
  UNION ALL SELECT 'dominos', 'Farmhouse Pizza', 'farmhouse-pizza', 299.00, 'images/food-items/dominos/farmhouse.jpg', 'Loaded with veggies like capsicum, onion and tomato.', 'veg'
  UNION ALL SELECT 'dominos', 'Veg Extravaganza', 'veg-extravaganza', 349.00, 'images/food-items/dominos/veg-extravaganza.jpg', 'Premium loaded pizza with exotic vegetables.', 'veg'
  UNION ALL SELECT 'dominos', 'Pepper BBQ Chicken', 'pepper-bbq-chicken', 399.00, 'images/food-items/dominos/pepper-bbq-chicken.jpg', 'Spicy BBQ chicken with cheese loaded crust.', 'nonveg'
  UNION ALL SELECT 'dominos', 'Garlic Breadsticks', 'garlic-breadsticks', 149.00, 'images/food-items/dominos/garlic-breadsticks.jpg', 'Freshly baked bread with garlic and herbs.', 'veg'
  UNION ALL SELECT 'dominos', 'Stuffed Garlic Bread', 'stuffed-garlic-bread', 179.00, 'images/food-items/dominos/stuffed-garlic-bread.jpg', 'Cheesy stuffed bread with herbs and seasoning.', 'veg'
  UNION ALL SELECT 'dominos', 'Pepsi', 'pepsi', 60.00, 'images/food-items/dominos/pepsi.jpg', 'Chilled soft drink.', 'none'
  UNION ALL SELECT 'dominos', 'Choco Lava Cake', 'choco-lava-cake', 99.00, 'images/food-items/dominos/choco-lava-cake.jpg', 'Warm chocolate cake with molten center.', 'veg'

  UNION ALL SELECT 'pizza-hut', 'Margherita Pizza', 'margherita-pizza', 199.00, 'images/food-items/pizza-hut/Margherita Pizza.jpg', 'Classic cheese pizza with rich tomato sauce and a soft baked crust.', 'veg'
  UNION ALL SELECT 'pizza-hut', 'Veggie Supreme Pizza', 'veggie-supreme-pizza', 289.00, 'images/food-items/pizza-hut/Veggie Supreme Pizza.jpg', 'Loaded with fresh vegetables, cheese and flavourful pizza sauce.', 'veg'
  UNION ALL SELECT 'pizza-hut', 'Chicken Supreme Pizza', 'chicken-supreme-pizza', 349.00, 'images/food-items/pizza-hut/Chicken Supreme pizza.jpg', 'Loaded with juicy chicken, veggies and melted cheese on a crispy base.', 'nonveg'
  UNION ALL SELECT 'pizza-hut', 'Tandoori Paneer Pizza', 'tandoori-paneer-pizza', 329.00, 'images/food-items/pizza-hut/Tandoori Paneer Pizza.jpg', 'Paneer chunks with smoky tandoori flavour, onions and cheesy topping.', 'veg'
  UNION ALL SELECT 'pizza-hut', 'Chicken Tikka Pizza', 'chicken-tikka-pizza', 359.00, 'images/food-items/pizza-hut/Chicken Tikka pizza.jpg', 'Spicy chicken tikka pizza with rich cheese and Indian-style flavours.', 'nonveg'
  UNION ALL SELECT 'pizza-hut', 'Garlic Bread with Cheese', 'garlic-bread-with-cheese', 159.00, 'images/food-items/pizza-hut/garlic-bread-cheese.jpg', 'Crispy garlic bread topped with melted cheese, perfect as a side.', 'veg'
  UNION ALL SELECT 'pizza-hut', 'Choco Chip Cookie', 'choco-chip-cookie', 99.00, 'images/food-items/pizza-hut/choco-chip-cookie.jpg', 'Warm and soft cookie loaded with delicious chocolate chips.', 'veg'
  UNION ALL SELECT 'pizza-hut', 'Iced Tea', 'iced-tea', 79.00, 'images/food-items/pizza-hut/iced-tea.jpg', 'Refreshing chilled iced tea to pair with your pizza meal.', 'none'

  UNION ALL SELECT 'meghana', 'Andhra Chicken Curry', 'andhra-chicken-curry', 279.00, 'images/food-items/meghana/andhra-chicken-curry.jpg', 'Spicy Andhra-style chicken curry made with rich masala and bold flavours.', 'nonveg'
  UNION ALL SELECT 'meghana', 'Chicken Fry Piece Biryani', 'chicken-fry-piece-biryani', 299.00, 'images/food-items/meghana/chicken-fry-piece-biryani.jpg', 'Flavorful biryani served with spicy fried chicken pieces and aromatic rice.', 'nonveg'
  UNION ALL SELECT 'meghana', 'Paneer Biryani', 'paneer-biryani', 249.00, 'images/food-items/meghana/paneer-biryani.jpg', 'Aromatic biryani rice cooked with paneer cubes and traditional spices.', 'veg'
  UNION ALL SELECT 'meghana', 'Apollo Fish', 'apollo-fish', 289.00, 'images/food-items/meghana/apollo-fish.jpg', 'Spicy fried fish tossed in South Indian style masala for a fiery starter.', 'nonveg'
  UNION ALL SELECT 'meghana', 'Chicken 65', 'chicken-65', 239.00, 'images/food-items/meghana/chicken-65.jpg', 'Crispy spicy chicken bites tossed with curry leaves and bold seasoning.', 'nonveg'
  UNION ALL SELECT 'meghana', 'Veg Meals', 'veg-meals', 199.00, 'images/food-items/meghana/veg-meals.jpg', 'Traditional South Indian veg meals served with rice, curry and sides.', 'veg'
  UNION ALL SELECT 'meghana', 'Double Ka Meetha', 'double-ka-meetha', 129.00, 'images/food-items/meghana/double-ka-meetha.jpg', 'Classic Hyderabad-style bread dessert soaked in sweet rich syrup.', 'veg'
  UNION ALL SELECT 'meghana', 'Buttermilk', 'buttermilk', 69.00, 'images/food-items/meghana/buttermilk.jpg', 'Cool and refreshing buttermilk that pairs perfectly with spicy meals.', 'none'

  UNION ALL SELECT 'paradise', 'Hyderabadi Chicken Dum Biryani', 'hyderabadi-chicken-dum-biryani', 299.00, 'images/food-items/paradise/hyderabadi-chicken-dum-biryani.jpg', 'Authentic dum biryani cooked with aromatic spices and tender chicken.', 'nonveg'
  UNION ALL SELECT 'paradise', 'Mutton Haleem', 'mutton-haleem', 279.00, 'images/food-items/paradise/mutton-haleem.jpg', 'Slow-cooked mutton stew with wheat and spices, rich and flavorful.', 'nonveg'
  UNION ALL SELECT 'paradise', 'Paneer Butter Masala', 'paneer-butter-masala', 249.00, 'images/food-items/paradise/paneer-butter-masala.jpg', 'Creamy paneer curry cooked in rich tomato gravy.', 'veg'
  UNION ALL SELECT 'paradise', 'Chicken Korma', 'chicken-korma', 269.00, 'images/food-items/paradise/chicken-korma.jpg', 'Mild and creamy chicken curry with traditional spices.', 'nonveg'
  UNION ALL SELECT 'paradise', 'Egg Biryani', 'egg-biryani', 229.00, 'images/food-items/paradise/egg-biryani.jpg', 'Flavorful biryani made with boiled eggs and fragrant rice.', 'egg'
  UNION ALL SELECT 'paradise', 'Veg Fried Rice', 'veg-fried-rice', 199.00, 'images/food-items/paradise/veg-fried-rice.jpg', 'Classic Indo-Chinese rice tossed with vegetables and light seasoning.', 'veg'
  UNION ALL SELECT 'paradise', 'Qubani Ka Meetha', 'qubani-ka-meetha', 149.00, 'images/food-items/paradise/qubani-ka-meetha.jpg', 'Traditional Hyderabadi dessert made from dried apricots.', 'veg'
  UNION ALL SELECT 'paradise', 'Sweet Lassi', 'sweet-lassi', 99.00, 'images/food-items/paradise/sweet-lassi.jpg', 'Refreshing sweet yogurt drink that pairs well with spicy dishes.', 'none'

  UNION ALL SELECT 'chinese-wok', 'Chicken Hakka Noodles', 'chicken-hakka-noodles', 229.00, 'images/food-items/chinese-wok/chicken-hakka-noodles.jpg', 'Wok-tossed noodles with tender chicken, vegetables and classic Chinese seasoning.', 'nonveg'
  UNION ALL SELECT 'chinese-wok', 'Veg Fried Rice', 'veg-fried-rice', 199.00, 'images/food-items/chinese-wok/veg-fried-rice.jpg', 'Fluffy rice stir-fried with fresh vegetables, sauces and aromatic seasonings.', 'veg'
  UNION ALL SELECT 'chinese-wok', 'Chilli Chicken', 'chilli-chicken', 249.00, 'images/food-items/chinese-wok/chilli-chicken.jpg', 'Spicy and flavorful chicken tossed with capsicum, onion and Chinese sauces.', 'nonveg'
  UNION ALL SELECT 'chinese-wok', 'Paneer Manchurian', 'paneer-manchurian', 219.00, 'images/food-items/chinese-wok/paneer-manchurian.jpg', 'Crispy paneer cubes coated in a rich Indo-Chinese sauce with crunchy veggies.', 'veg'
  UNION ALL SELECT 'chinese-wok', 'Chicken Spring Roll', 'chicken-spring-roll', 179.00, 'images/food-items/chinese-wok/chicken-spring-roll.jpg', 'Crispy golden rolls stuffed with juicy chicken and savory oriental flavours.', 'nonveg'
  UNION ALL SELECT 'chinese-wok', 'Veg Momos', 'veg-momos', 169.00, 'images/food-items/chinese-wok/veg-momos.jpg', 'Soft steamed dumplings filled with seasoned vegetables and served as a light snack.', 'veg'
  UNION ALL SELECT 'chinese-wok', 'Chocolate Brownie', 'chocolate-brownie', 129.00, 'images/food-items/chinese-wok/chocolate-brownie.jpg', 'Rich and fudgy chocolate brownie for a sweet finish after your meal.', 'veg'
  UNION ALL SELECT 'chinese-wok', 'Peach Iced Tea', 'peach-iced-tea', 99.00, 'images/food-items/chinese-wok/peach-iced-tea.jpg', 'Refreshing chilled iced tea with a sweet peach flavour to complement your meal.', 'none'

  UNION ALL SELECT 'mainland-china', 'Chicken Manchow Soup', 'chicken-manchow-soup', 189.00, 'images/food-items/mainland-china/chicken-manchow-soup.jpg', 'Hot and flavorful chicken soup with vegetables and crunchy toppings.', 'nonveg'
  UNION ALL SELECT 'mainland-china', 'Veg Spring Rolls', 'veg-spring-rolls', 179.00, 'images/food-items/mainland-china/veg-spring-rolls.jpg', 'Crispy rolls stuffed with seasoned vegetables and served as a light starter.', 'veg'
  UNION ALL SELECT 'mainland-china', 'Kung Pao Chicken', 'kung-pao-chicken', 289.00, 'images/food-items/mainland-china/kung-pao-chicken.jpg', 'Spicy stir-fried chicken tossed with peppers and rich Chinese sauces.', 'nonveg'
  UNION ALL SELECT 'mainland-china', 'Schezwan Noodles', 'schezwan-noodles', 229.00, 'images/food-items/mainland-china/schezwan-noodles.jpg', 'Wok-tossed noodles in spicy schezwan sauce with vegetables.', 'veg'
  UNION ALL SELECT 'mainland-china', 'Veg Fried Rice', 'veg-fried-rice', 219.00, 'images/food-items/mainland-china/veg-fried-rice.jpg', 'Classic fried rice tossed with fresh vegetables and Chinese seasoning.', 'veg'
  UNION ALL SELECT 'mainland-china', 'Chili Garlic Prawns', 'chili-garlic-prawns', 319.00, 'images/food-items/mainland-china/chili-garlic-prawns.jpg', 'Juicy prawns cooked with chili, garlic and savory oriental flavors.', 'nonveg'
  UNION ALL SELECT 'mainland-china', 'Sesame Balls', 'sesame-balls', 149.00, 'images/food-items/mainland-china/sesame-balls.jpg', 'Crispy golden sesame balls filled with sweet paste, a classic Chinese dessert.', 'veg'
  UNION ALL SELECT 'mainland-china', 'Jasmine Tea', 'jasmine-tea', 99.00, 'images/food-items/mainland-china/jasmine-tea.jpg', 'Light and aromatic jasmine tea that pairs perfectly with Chinese meals.', 'none'

  UNION ALL SELECT 'empire', 'Chicken Biryani', 'chicken-biryani', 249.00, 'images/food-items/empire/chicken-biryani.jpg', 'Aromatic basmati rice cooked with juicy chicken and rich spices.', 'nonveg'
  UNION ALL SELECT 'empire', 'Mutton Biryani', 'mutton-biryani', 299.00, 'images/food-items/empire/mutton-biryani.jpg', 'Flavorful mutton cooked with basmati rice and authentic spices.', 'nonveg'
  UNION ALL SELECT 'empire', 'Butter Chicken', 'butter-chicken', 279.00, 'images/food-items/empire/butter-chicken.jpg', 'Creamy tomato-based curry with tender chicken pieces.', 'nonveg'
  UNION ALL SELECT 'empire', 'Chicken Kebab', 'chicken-kebab', 199.00, 'images/food-items/empire/chicken-kebab.jpg', 'Juicy grilled chicken kebabs with smoky flavor.', 'nonveg'
  UNION ALL SELECT 'empire', 'Tandoori Chicken', 'tandoori-chicken', 259.00, 'images/food-items/empire/tandoori-chicken.jpg', 'Classic tandoori chicken marinated and roasted to perfection.', 'nonveg'
  UNION ALL SELECT 'empire', 'Chicken Shawarma', 'chicken-shawarma', 179.00, 'images/food-items/empire/chicken-shawarma.jpg', 'Soft wrap filled with spiced chicken and creamy sauce.', 'nonveg'
  UNION ALL SELECT 'empire', 'Gulab Jamun', 'gulab-jamun', 99.00, 'images/food-items/empire/gulab-jamun.jpg', 'Soft and sweet syrup-soaked dessert balls.', 'veg'
  UNION ALL SELECT 'empire', 'Lassi', 'lassi', 79.00, 'images/food-items/empire/lassi.jpg', 'Refreshing yogurt-based drink, perfect with spicy meals.', 'none'

  UNION ALL SELECT 'punjab-grill', 'Amritsari Fish Tikka', 'amritsari-fish-tikka', 299.00, 'images/food-items/punjab-grill/amritsari-fish-tikka.jpg', 'Crispy and flavorful Punjabi-style fish tikka with bold spices.', 'nonveg'
  UNION ALL SELECT 'punjab-grill', 'Dal Makhani', 'dal-makhani', 229.00, 'images/food-items/punjab-grill/dal-makhani.jpg', 'Slow-cooked black lentils in a creamy buttery Punjabi gravy.', 'veg'
  UNION ALL SELECT 'punjab-grill', 'Paneer Tikka', 'paneer-tikka', 249.00, 'images/food-items/punjab-grill/paneer-tikka.jpg', 'Smoky grilled paneer cubes marinated in spiced yogurt.', 'veg'
  UNION ALL SELECT 'punjab-grill', 'Butter Naan', 'butter-naan', 69.00, 'images/food-items/punjab-grill/butter-naan.jpg', 'Soft naan brushed with butter, perfect with rich curries.', 'veg'
  UNION ALL SELECT 'punjab-grill', 'Chicken Tikka Masala', 'chicken-tikka-masala', 279.00, 'images/food-items/punjab-grill/chicken-tikka-masala.jpg', 'Tender chicken tikka cooked in a creamy tomato gravy.', 'nonveg'
  UNION ALL SELECT 'punjab-grill', 'Jeera Rice', 'jeera-rice', 149.00, 'images/food-items/punjab-grill/jeera-rice.jpg', 'Fragrant basmati rice tempered with cumin and light spices.', 'veg'
  UNION ALL SELECT 'punjab-grill', 'Phirni', 'phirni', 129.00, 'images/food-items/punjab-grill/phirni.jpg', 'Traditional Punjabi rice pudding served chilled and creamy.', 'veg'
  UNION ALL SELECT 'punjab-grill', 'Sweet Lime Soda', 'sweet-lime-soda', 99.00, 'images/food-items/punjab-grill/sweet-lime-soda.jpg', 'Refreshing sparkling lime drink with a sweet citrus taste.', 'none'

  UNION ALL SELECT 'udupi', 'Masala Dosa', 'masala-dosa', 95.00, 'images/food-items/udupi/masala-dosa.jpg', 'Golden crispy dosa filled with spiced potato masala, served with chutney and sambar.', 'veg'
  UNION ALL SELECT 'udupi', 'Idli Vada', 'idli-vada', 85.00, 'images/food-items/udupi/idli-vada.jpg', 'Soft idlis paired with crispy medu vada, served with coconut chutney and hot sambar.', 'veg'
  UNION ALL SELECT 'udupi', 'Khara Bath', 'khara-bath', 80.00, 'images/food-items/udupi/khara-bath.jpg', 'Flavourful semolina breakfast dish cooked with vegetables, spices and a rich South Indian touch.', 'veg'
  UNION ALL SELECT 'udupi', 'Bisibele Bath', 'bisibele-bath', 110.00, 'images/food-items/udupi/bisibele-bath.jpg', 'A comforting Karnataka-style rice dish made with lentils, vegetables and aromatic spices.', 'veg'
  UNION ALL SELECT 'udupi', 'Poori Saagu', 'poori-saagu', 90.00, 'images/food-items/udupi/poori-saagu.jpg', 'Puffed soft pooris served with mildly spiced vegetable saagu for a hearty meal.', 'veg'
  UNION ALL SELECT 'udupi', 'Filter Coffee', 'filter-coffee', 35.00, 'images/food-items/udupi/filter-coffee.jpg', 'Classic strong South Indian filter coffee with a rich aroma and smooth taste.', 'none'
  UNION ALL SELECT 'udupi', 'Kesari Bath', 'kesari-bath', 70.00, 'images/food-items/udupi/kesari-bath.jpg', 'Sweet semolina dessert with ghee, saffron notes and crunchy dry fruits.', 'veg'
  UNION ALL SELECT 'udupi', 'Curd Rice', 'curd-rice', 75.00, 'images/food-items/udupi/curd-rice.jpg', 'Cool and comforting curd rice tempered with mustard, curry leaves and mild seasoning.', 'veg'

  UNION ALL SELECT 'vidyarthi', 'Butter Masala Dosa', 'butter-masala-dosa', 110.00, 'images/food-items/vidyarthi/butter-masala-dosa.jpg', 'A crispy golden dosa layered with butter and filled with flavourful potato masala.', 'veg'
  UNION ALL SELECT 'vidyarthi', 'Plain Dosa', 'plain-dosa', 75.00, 'images/food-items/vidyarthi/plain-dosa.jpg', 'Classic thin and crispy dosa served with fresh chutney and hot sambar.', 'veg'
  UNION ALL SELECT 'vidyarthi', 'Set Dosa', 'set-dosa', 90.00, 'images/food-items/vidyarthi/set-dosa.jpg', 'Soft, fluffy mini dosas served as a comforting South Indian breakfast favourite.', 'veg'
  UNION ALL SELECT 'vidyarthi', 'Rava Vada', 'rava-vada', 65.00, 'images/food-items/vidyarthi/rava-vada.jpg', 'Crispy semolina vada with a crunchy bite, served with chutney on the side.', 'veg'
  UNION ALL SELECT 'vidyarthi', 'Pongal', 'pongal', 85.00, 'images/food-items/vidyarthi/pongal.jpg', 'Warm and comforting rice-lentil dish cooked with ghee, pepper and mild spices.', 'veg'
  UNION ALL SELECT 'vidyarthi', 'Badam Milk', 'badam-milk', 55.00, 'images/food-items/vidyarthi/badam-milk.jpg', 'Sweet and creamy almond milk drink with a rich traditional flavour.', 'none'
  UNION ALL SELECT 'vidyarthi', 'Chow Chow Bath', 'chow-chow-bath', 95.00, 'images/food-items/vidyarthi/chow-chow-bath.jpg', 'A beloved combo of khara bath and sweet kesari bath served together on one plate.', 'veg'
  UNION ALL SELECT 'vidyarthi', 'Lemon Rice', 'lemon-rice', 80.00, 'images/food-items/vidyarthi/lemon-rice.jpg', 'Tangy rice tempered with mustard, curry leaves and peanuts for a light meal.', 'veg'

  UNION ALL SELECT 'polar-bear', 'Chocolate Ice Cream', 'chocolate-ice-cream', 99.00, 'images/food-items/polar-bear/chocolate-ice-cream.jpg', 'Rich and creamy chocolate ice cream for a classic dessert treat.', 'veg'
  UNION ALL SELECT 'polar-bear', 'Butterscotch Ice Cream', 'butterscotch-ice-cream', 99.00, 'images/food-items/polar-bear/butterscotch-ice-cream.jpg', 'Creamy butterscotch ice cream with sweet caramel flavor and crunch.', 'veg'
  UNION ALL SELECT 'polar-bear', 'Strawberry Ice Cream', 'strawberry-ice-cream', 99.00, 'images/food-items/polar-bear/strawberry-ice-cream.jpg', 'Sweet and fruity strawberry ice cream with a smooth refreshing taste.', 'veg'
  UNION ALL SELECT 'polar-bear', 'Chocolate Sundae', 'chocolate-sundae', 149.00, 'images/food-items/polar-bear/chocolate-sundae.jpg', 'Delicious sundae layered with rich chocolate sauce and creamy ice cream.', 'veg'
  UNION ALL SELECT 'polar-bear', 'Brownie with Ice Cream', 'brownie-with-ice-cream', 179.00, 'images/food-items/polar-bear/brownie-with-ice-cream.jpg', 'Warm brownie served with a scoop of creamy ice cream and dessert sauce.', 'veg'
  UNION ALL SELECT 'polar-bear', 'Banana Split', 'banana-split', 169.00, 'images/food-items/polar-bear/banana-split.jpg', 'Classic banana split topped with ice cream, syrup and sweet crunchy toppings.', 'veg'
  UNION ALL SELECT 'polar-bear', 'Mango Ice Cream', 'mango-ice-cream', 109.00, 'images/food-items/polar-bear/mango-ice-cream.jpg', 'Refreshing mango ice cream with fruity flavor and creamy texture.', 'veg'
  UNION ALL SELECT 'polar-bear', 'Cold Coffee', 'cold-coffee', 129.00, 'images/food-items/polar-bear/cold-coffee.jpg', 'Chilled coffee blended smooth for a refreshing cafe-style dessert drink.', 'none'

  UNION ALL SELECT 'corner-house', 'Death By Chocolate', 'death-by-chocolate', 189.00, 'images/food-items/corner-house/death-by-chocolate.jpg', 'Signature layered chocolate dessert loaded with ice cream and rich fudge.', 'veg'
  UNION ALL SELECT 'corner-house', 'Hot Chocolate Fudge', 'hot-chocolate-fudge', 179.00, 'images/food-items/corner-house/hot-chocolate-fudge.jpg', 'Warm chocolate sauce poured over vanilla ice cream and brownie.', 'veg'
  UNION ALL SELECT 'corner-house', 'Caramel Sundae', 'caramel-sundae', 159.00, 'images/food-items/corner-house/caramel-sundae.jpg', 'Creamy ice cream topped with rich caramel drizzle and crunchy bits.', 'veg'
  UNION ALL SELECT 'corner-house', 'Brownie With Ice Cream', 'brownie-with-ice-cream', 169.00, 'images/food-items/corner-house/brownie-with-ice-cream.jpg', 'Warm brownie served with a scoop of vanilla ice cream.', 'veg'
  UNION ALL SELECT 'corner-house', 'Vanilla Ice Cream', 'vanilla-ice-cream', 99.00, 'images/food-items/corner-house/vanilla-ice-cream.jpg', 'Classic creamy vanilla ice cream loved by all.', 'veg'
  UNION ALL SELECT 'corner-house', 'Strawberry Milkshake', 'strawberry-milkshake', 129.00, 'images/food-items/corner-house/strawberry-milkshake.jpg', 'Sweet strawberry blended milkshake with a smooth texture.', 'none'
  UNION ALL SELECT 'corner-house', 'Chocolate Milkshake', 'chocolate-milkshake', 139.00, 'images/food-items/corner-house/chocolate-milkshake.jpg', 'Rich chocolate milkshake topped with creamy goodness.', 'none'
  UNION ALL SELECT 'corner-house', 'Cold Coffee', 'cold-coffee', 119.00, 'images/food-items/corner-house/cold-coffee.jpg', 'Chilled coffee blended with milk and ice for a refreshing drink.', 'none'

  UNION ALL SELECT 'freshmenu', 'Chicken Avocado Salad', 'chicken-avocado-salad', 259.00, 'images/food-items/freshmenu/chicken-avocado-salad.jpg', 'Fresh greens topped with grilled chicken, avocado, cherry tomatoes and seeds.', 'nonveg'
  UNION ALL SELECT 'freshmenu', 'Mediterranean Veg Salad', 'mediterranean-veg-salad', 229.00, 'images/food-items/freshmenu/mediterranean-veg-salad.jpg', 'A vibrant mix of lettuce, olives, cucumber, tomatoes and fresh Mediterranean flavours.', 'veg'
  UNION ALL SELECT 'freshmenu', 'Smoked Chicken Wrap', 'smoked-chicken-wrap', 219.00, 'images/food-items/freshmenu/smoked-chicken-wrap.jpg', 'Soft wrap loaded with smoked chicken, crunchy veggies and creamy dressing.', 'nonveg'
  UNION ALL SELECT 'freshmenu', 'Pesto Paneer Sandwich', 'pesto-paneer-sandwich', 199.00, 'images/food-items/freshmenu/pesto-paneer-sandwich.jpg', 'Grilled sandwich filled with paneer, fresh vegetables and a flavorful pesto spread.', 'veg'
  UNION ALL SELECT 'freshmenu', 'Caesar Chicken Bowl', 'caesar-chicken-bowl', 249.00, 'images/food-items/freshmenu/caesar-chicken-bowl.jpg', 'A hearty bowl with chicken, crisp lettuce, crunchy toppings and creamy Caesar dressing.', 'nonveg'
  UNION ALL SELECT 'freshmenu', 'Roasted Veggie Bowl', 'roasted-veggie-bowl', 219.00, 'images/food-items/freshmenu/roasted-veggie-bowl.jpg', 'A wholesome bowl of roasted vegetables, greens and grains with a fresh dressing.', 'veg'
  UNION ALL SELECT 'freshmenu', 'Mango Yogurt Parfait', 'mango-yogurt-parfait', 139.00, 'images/food-items/freshmenu/mango-yogurt-parfait.jpg', 'Layered mango yogurt parfait with fruit and crunchy toppings for a light dessert.', 'veg'
  UNION ALL SELECT 'freshmenu', 'Watermelon Cooler', 'watermelon-cooler', 109.00, 'images/food-items/freshmenu/watermelon-cooler.jpg', 'Refreshing watermelon-based cooler that pairs perfectly with fresh light meals.', 'none'

  UNION ALL SELECT 'eatfit', 'Grilled Chicken Salad', 'grilled-chicken-salad', 249.00, 'images/food-items/eatfit/grilled-chicken-salad.jpg', 'Fresh greens topped with grilled chicken, veggies and light dressing.', 'nonveg'
  UNION ALL SELECT 'eatfit', 'Paneer Protein Bowl', 'paneer-protein-bowl', 229.00, 'images/food-items/eatfit/paneer-protein-bowl.jpg', 'High-protein bowl with paneer, veggies and balanced nutrition.', 'veg'
  UNION ALL SELECT 'eatfit', 'Veg Quinoa Bowl', 'veg-quinoa-bowl', 219.00, 'images/food-items/eatfit/veg-quinoa-bowl.jpg', 'Healthy quinoa bowl packed with veggies and nutrients.', 'veg'
  UNION ALL SELECT 'eatfit', 'Chicken Brown Rice Meal', 'chicken-brown-rice-meal', 269.00, 'images/food-items/eatfit/chicken-brown-rice.jpg', 'Lean chicken served with brown rice for a balanced meal.', 'nonveg'
  UNION ALL SELECT 'eatfit', 'Veg Wrap', 'veg-wrap', 179.00, 'images/food-items/eatfit/veg-wrap.jpg', 'Soft wrap filled with fresh veggies and healthy sauces.', 'veg'
  UNION ALL SELECT 'eatfit', 'Chia Seed Pudding', 'chia-seed-pudding', 159.00, 'images/food-items/eatfit/chia-seed-pudding.jpg', 'Healthy chia pudding topped with fresh fruits and natural sweetness.', 'veg'
  UNION ALL SELECT 'eatfit', 'Fruit Yogurt Bowl', 'fruit-yogurt-bowl', 149.00, 'images/food-items/eatfit/fruit-yogurt-bowl.jpg', 'Fresh fruits with creamy yogurt for a light healthy snack.', 'veg'
  UNION ALL SELECT 'eatfit', 'Green Tea', 'green-tea', 79.00, 'images/food-items/eatfit/green-tea.jpg', 'Light and refreshing green tea for a healthy lifestyle.', 'none'

  UNION ALL SELECT 'hae-kum-gang', 'Bibimbap', 'bibimbap', 249.00, 'images/food-items/hae-kum-gang/bibimbap.jpg', 'Korean rice bowl with vegetables, sauces and authentic flavours.', 'veg'
  UNION ALL SELECT 'hae-kum-gang', 'Chicken Bulgogi', 'chicken-bulgogi', 269.00, 'images/food-items/hae-kum-gang/chicken-bulgogi.jpg', 'Sweet and savory Korean marinated chicken grilled to perfection.', 'nonveg'
  UNION ALL SELECT 'hae-kum-gang', 'Chocolate Mochi', 'chocolate-mochi', 149.00, 'images/food-items/hae-kum-gang/chocolate-mochi.jpg', 'Soft and chewy dessert with rich chocolate filling.', 'veg'
  UNION ALL SELECT 'hae-kum-gang', 'Kimchi Fried Rice', 'kimchi-fried-rice', 229.00, 'images/food-items/hae-kum-gang/kimchi-fried-rice.jpg', 'Spicy fried rice tossed with kimchi and Korean seasoning.', 'veg'
  UNION ALL SELECT 'hae-kum-gang', 'Korean Fried Chicken', 'korean-fried-chicken', 279.00, 'images/food-items/hae-kum-gang/korean-fried-chicken.jpg', 'Crispy chicken coated in spicy Korean glaze.', 'nonveg'
  UNION ALL SELECT 'hae-kum-gang', 'Korean Ramen Bowl', 'korean-ramen-bowl', 249.00, 'images/food-items/hae-kum-gang/korean-ramen-bowl.jpg', 'Hot and spicy ramen with rich broth and toppings.', 'nonveg'
  UNION ALL SELECT 'hae-kum-gang', 'Korean Lemon Ade', 'korean-lemon-ade', 129.00, 'images/food-items/hae-kum-gang/lemon-ade.jpg', 'Refreshing sparkling lemon drink with a sweet and tangy Korean twist.', 'none'
  UNION ALL SELECT 'hae-kum-gang', 'Veg Kimbap', 'veg-kimbap', 199.00, 'images/food-items/hae-kum-gang/veg-kimbap.jpg', 'Korean sushi rolls filled with vegetables and rice.', 'veg'
) AS seed
  ON seed.restaurant_slug = r.slug
ON DUPLICATE KEY UPDATE
  name = VALUES(name),
  price = VALUES(price),
  image = VALUES(image),
  description = VALUES(description),
  food_type = VALUES(food_type),
  is_available = VALUES(is_available);

COMMIT;
