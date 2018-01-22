<?php
// ######## please do not alter the following code ########
$products = array(
    array("name" => "Sledgehammer", "price" => 125.75),
    array("name" => "Axe", "price" => 190.50),
    array("name" => "Bandsaw", "price" => 562.13),
    array("name" => "Chisel", "price" => 12.9),
    array("name" => "Hacksaw", "price" => 18.45)
);
// ##################################################

function getProductByName($products, $name) {
    foreach ($products as $product) {
        if ($product["name"] === $name) {
            return Product::create($product);
        }
    }
    return null;
}

function twoDp($price) {
    return number_format($price, 2, ".", "");
}

class ShoppingCart {
    private $items = array();

    public function addItem($product) {
        $key = $product->getName();
        if (array_key_exists($key, $this->items)) {
            $this->items[$key]->addToQuantity($product);
        } else {
            $this->items[$key] = new ShoppingCartItem($product);
        }
    }

    public function removeItem($product) {
        unset($this->items[$product->getName()]);
    }

    public function isEmpty() {
        return count($this->items) === 0;
    }

    public function getItems() {
        return array_values($this->items);
    }

    public function getTotal() {
        return array_reduce($this->items, function ($carry, $item) {
            return $carry + $item->getTotal();
        }, 0);
    }
}

class ShoppingCartItem {
    private $quantity = array();

    function __construct($product) {
        $this->addToQuantity($product);
    }

    public function getName() {
        return $this->quantity[0]->getName();
    }

    public function getPrice() {
        return $this->quantity[0]->getPrice();
    }

    public function addToQuantity(Product $product) {
        array_push($this->quantity, $product);
    }

    public function getQuantity() {
        return count($this->quantity);
    }

    public function getTotal() {
        return $this->getQuantity() * $this->getPrice();
    }
}


class Product {
    static public function create($productArray) {
        return new Product($productArray["name"], $productArray["price"]);
    }

    function __construct($name, $price) {
        $this->name = $name;
        $this->price = $price;
    }

    public function getPrice() {
        return $this->price;
    }

    public function getName() {
        return $this->name;
    }
}

session_start();

$productList = array_map(function ($product) {
    return Product::create($product);
}, $products);

$shoppingCart = new ShoppingCart();

if (isset($_SESSION["shoppingCart"])) {
    $shoppingCart = $_SESSION["shoppingCart"];
}

if (isset($_POST["addToCart"])) {
    $context = getProductByName($products, $_POST["addToCart"]);
    $shoppingCart->addItem($context);
} else if (isset($_POST["removeFromCart"])) {
    $context = getProductByName($products, $_POST["removeFromCart"]);
    $shoppingCart->removeItem($context);
}

$_SESSION["shoppingCart"] = $shoppingCart;
?>

<!doctype html>
<html class="no-js" lang="">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="x-ua-compatible" content="ie=edge">
        <title>A simple PHP shopping cart app</title>
        <meta name="description" content="">
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

        <link rel="manifest" href="site.webmanifest">
        <link rel="apple-touch-icon" href="icon.png">
        <!-- Place favicon.ico in the root directory -->

        <link href="https://fonts.googleapis.com/css?family=Lato:300,400"
                rel="stylesheet">
        <link rel="stylesheet" href="dist/bundle.css">
    </head>
    <body>
        <!--[if lte IE 9]>
            <p class="browserupgrade">
                You are using an <strong>outdated</strong> browser. Please
                <a href="https://browsehappy.com/">upgrade your browser</a>
                to improve your experience and security.
            </p>
        <![endif]-->

        <div id="shopping-cart">
            <div class="product-list">
                <h2>Products</h2>
                <ul>
                    <?php foreach ($productList as $product): ?>
                    <li class="product-list__product">
                        <form method="POST">
                            <input type="hidden"
                                    name="addToCart"
                                    value="<?php echo $product->getName(); ?>">
                            <span class="product__name">
                                <?php echo $product->getName(); ?>
                            </span>
                            <span class="product__price">
                                $<?php echo twoDp($product->getPrice()); ?>
                            </span>
                            <button class="product__add-to-cart">
                                Add to cart
                            </button>
                        </form>
                    </li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <div class="shopping-cart">
                <h2>Shopping cart</h2>
                <?php if ($shoppingCart->isEmpty()): ?>
                <p class="shopping-cart__empty-message">
                    Your shopping cart is empty.
                </p>
                <?php endif; ?>
                <ul>
                    <?php foreach ($shoppingCart->getItems() as $cartItem): ?>
                    <li class="shopping-cart__item">
                        <form method="POST">
                            <input type="hidden"
                                    name="removeFromCart"
                                    value="<?php echo $cartItem->getName(); ?>">
                            <span class="cart-item__name">
                                <?php echo $cartItem->getName(); ?>
                            </span>
                            <span>
                                <span>
                                    <?php echo $cartItem->getQuantity(); ?>
                                </span>
                                <span class="quantity__label">qty</span>
                            </span>
                            <span class="cart-item__price">
                                <span>
                                    $<?php echo twoDp($cartItem->getPrice()); ?>
                                </span>
                                <span class="price__label">each</span>
                            </span>
                            <span class="cart-item__total">
                                $<?php echo twoDp($cartItem->getTotal()); ?>
                            </span>
                            <button class="cart-item__remove">
                                Remove
                            </button>
                        </form>
                    </li>
                    <?php endforeach; ?>
                </ul>
                <?php if (!$shoppingCart->isEmpty()): ?>
                <p class="shopping-cart__total">
                    <span class="total__label">Total</span>
                    $<?php echo twoDp($shoppingCart->getTotal()); ?>
                </p>
                <?php endif; ?>
            </div>
        </div>
    </body>
</html>
