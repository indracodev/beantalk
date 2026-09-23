# Supresso Coffee — Complete Chatbot Flow Tree & Verbatim Transcription

> **Source Document:** `chatbot SSG.pdf`
> **Type:** Full Verbatim Conversion (Original English copy preserved with zero alterations)
> **Domain:** Coffee E-Commerce Customer Support Automation (Supresso Coffee)

---

## 1. Top-Level Architectural Map

```text
[ ROOT: Main Welcome Greeting ]
  │
  ├── 1. Order
  │     ├── Order status
  │     ├── Cancelation
  │     └── Return policy
  │
  ├── 2. Product (We are here to assist you)
  │     ├── Cannot Find Product
  │     ├── Add to cart error
  │     └── Price Adjustment
  │
  ├── 3. Membership
  │     ├── Account login (Account login issue)
  │     │     └── Cannot login (Cannot login to account)
  │     │           ├── Reset password
  │     │           └── Forgot password
  │     ├── Suspicious activity
  │     └── Unsubscribe (Unsubscribe from promotional information)
  │
  ├── 4. About event
  │     └── Partnership or bulk (Information about partnership or bulk purchase)
  │           ├── How to be a Distributor
  │           ├── Promote our products
  │           ├── Office needs
  │           └── Bulk order
  │
  ├── 5. Checkout (We are here to assist you)
  │     ├── Kris+ Voucher
  │     ├── Checkout button
  │     ├── Payment
  │     │     ├── Credit/Debit Cards
  │     │     ├── Shop Pay Installment
  │     │     └── Price Adjustment
  │     └── Order issue (We are here to help your order issue)
  │           ├── Package
  │           │     ├── I haven't received my order
  │           │     └── Shipment issue
  │           │           ├── Shipment
  │           │           │     └── Cannot add full address for shipment
  │           │           └── We are here to help your shipment issue
  │           └── Information and partnership (What do you need?)
  │                 ├── Information
  │                 │     ├── Catalog
  │                 │     └── Current Promotion (Coffee deals just for you ☕)
  │                 │           ├── Summer Sales
  │                 │           ├── Father's Day
  │                 │           └── Payday
  │                 └── How to buy our coffee
  │                       ├── Buy from Shopee
  │                       ├── Buy from Lazada
  │                       └── Buy from Website
  │
  ├── 6. Global Shipping
  │     ├── Shipping option
  │     ├── Currency
  │     └── Taxes & Duties
  │
  ├── 7. Find what you need
  │     ├── Kris+ Voucher
  │     └── Shipping option
  │
  ├── 8. Product recommendation (Coffee Variety)
  │     ├── Our houseblend coffee
  │     ├── Our arabica coffee
  │     └── Our robusta coffee
  │
  ├── 9. Marketing
  │     ├── Marketing & promotional information
  │     │     ├── Email Marketing
  │     │     └── WhatsApp Marketing
  │
  ├── 10. Feedback
  │      └── Customer experience
  │            └── Product Care
  │
  └── sesuain lagi promo dari IPL
```

---

## 2. Complete Verbatim Node-by-Node Directory

### ROOT: Main Welcome Greeting
- **Node:** `root`
- **Verbatim Message (Yellow box):**
  > "Hi {{system::customer_name}}, thank you for reaching out Supresso Coffee. How we may assist you? :)"

---

### BRANCH 1: Order
- **Node:** `order`
- **Label:** "order (We are here to assist you)"

#### 1.1 Order status
- **Node:** `order_status`
- **Verbatim Message:**
  > "Orders are typically fulfilled within 2-3 business days. Once your order has been fulfilled..."

#### 1.2 Cancelation
- **Node:** `cancelation`
- **Verbatim Message:**
  > "We process and complete orders as quickly as possible. If you have any modification requests or..."

#### 1.3 Return policy
- **Node:** `return_policy`
- **Verbatim Message:**
  > "All forms of order errors and damage to goods during delivery are not our responsibility. Please..."

---

### BRANCH 2: Product
- **Node:** `product`
- **Label:** "Product (We are here to assist you)"

#### 2.1 Cannot Find Product
- **Node:** `cannot_find_product`
- **Verbatim Message:**
  > "Thank you for shopping with Supresso Coffee.
  >
  > Kindly fill up below the product information:
  > Product name:
  >
  > We'll get back to you with further information :)"

#### 2.2 Add to cart error
- **Node:** `add_to_cart_error`
- **Verbatim Message:**
  > "Thank you for shopping with Supresso Coffee.
  >
  > Kindly fill up below the information:
  > Username:"

#### 2.3 Price Adjustment
- **Node:** `price_adjustment_product`
- **Verbatim Message:**
  > "supresso.com does not have a price match guarantee.
  >
  > Should you have anymore questions, do not hesitate to contact us!"

---

### BRANCH 3: Membership
- **Node:** `membership`

#### 3.1 Account login (Account login issue)
- **Node:** `account_login`

##### 3.1.1 Cannot login (Cannot login to account)
- **Node:** `cannot_login`

###### 3.1.1.1 Reset password
- **Node:** `reset_password`

###### 3.1.1.2 Forgot password
- **Node:** `forgot_password`

#### 3.2 Suspicious activity
- **Node:** `suspicious_activity`
- **Verbatim Message:**
  > "Kindly provide your email address to proceed further, e.g. yourname@mail.com."

#### 3.3 Unsubscribe (Unsubscribe from promotional information)
- **Node:** `unsubscribe`

---

### BRANCH 4: About event
- **Node:** `about_event`

#### 4.1 Partnership or bulk (Information about partnership or bulk purchase)
- **Node:** `partnership_or_bulk`

##### 4.1.1 How to be a Distributor
- **Node:** `how_to_be_distributor`
- **Verbatim Message:**
  > "For Supresso Coffee, our distributors will place orders directly for our fresh coffee from our manufacturer in Indonesia, with a minimum order of 1 pallet. Please feel free to send your inquiries of the product you're interested in to our email:
  > sg@supresso.com / adm_si@supresso.com for more information, or simply chat with us here. Thank you!
  >
  > Should you have anymore questions, do not hesitate to contact us!"

##### 4.1.2 Promote our products
- **Node:** `promote_our_products`

##### 4.1.3 Office needs
- **Node:** `office_needs`
- **Verbatim Message:**
  > "Supresso Coffee is available to supply coffee for offices, hotel and other business sectors.
  >
  > Get a quote by sending us email at sg@supresso.com / adm_si@supresso.com or contact us at +65 8792 0780 for more information."

##### 4.1.4 Bulk order
- **Node:** `bulk_order`
- **Verbatim Message (Wholesale):**
  > "Supresso Coffee is available for wholesale purchase with minimum order quantity. The shipment will be shipped directly from our manufacurer in Indonesia to your door (terms and conditions apply)."
- **Verbatim Message (Endorsement):**
  > "Supresso Coffee is open for endorsement. Kindly send your proposal to our email sg@supresso.com / adm_si@supresso.com to get more info or simply chat with us here. Thank you!
  >
  > Should you have anymore questions, do not hesitate to contact us!"

##### 4.1.5 (Event supply)
- **Verbatim Message:**
  > "Supresso Coffee is available to supply coffee for your events such as weddings, private parties, etc.
  >
  > Please click here to fill in your order details and we will get back to you with quotation or contact us at +65 8792 0780 for more information.
  >
  > Should you have anymore questions, do not hesitate to contact us!"

##### 4.1.6 (General inquiry)
- **Verbatim Message:**
  > "Kindly fill up below the information:
  > Username:
  >
  > We'll check with our team and get back to you the soonest!"

##### 4.1.7 (Leaving / Unsubscribe message)
- **Verbatim Message:**
  > "We're sorry to see you go! If you're still interested in purchasing our products, you can find them on supresso.com, Shopee, and Lazada. We'll also be offering more deals soon. Hope to see you again!
  >
  > Should you have anymore questions, do not hesitate to contact us!"

---

### BRANCH 5: Checkout
- **Node:** `checkout`
- **Label:** "Checkout (We are here to assist you)"

#### 5.1 Kris+ Voucher
- **Node:** `kris_voucher`

#### 5.2 Checkout button
- **Node:** `checkout_button`

#### 5.3 Payment
- **Node:** `payment`
- **Verbatim Message:**
  > "Thank you for shopping with Supresso Coffee."

##### 5.3.1 Credit/Debit Cards
- **Node:** `credit_debit_cards`
- **Verbatim Message:**
  > "Credit/Debit Cards: We accept Visa, Mastercard, and American Express worldwide.
  >
  > Paypal: Available worldwide. At checkout, choose Paypal as the payment method and log into your PayPal account. Use any form of payment within your Paypal account.
  >
  > Apple Pay: Available in certain countries (learn more at here) and on mobile version only.
  >
  > Google Pay: Available worldwide (learn more at here).
  >
  > Union Pay: Available in certain countries (learn more at here).
  >
  > Shop Pay: Shop Pay and Shop Pay Installments are available in United States. See article "Shop Pay Installments" below for details.
  >
  > Should you have anymore questions, do not hesitate to contact us!"

##### 5.3.2 Shop Pay Installment
- **Node:** `shop_pay_installment`
- **Verbatim Message:**
  > "To pay for an order using Shop Pay Installments, the following eligibility criteria apply:
  >
  > · You need to have a United States billing address, and be signed up for Shop Pay.
  > · Your order needs to be between 50 USD and 20,000 USD, including shipping and taxes.
  > · If you're ordering a physical product that requires shipping, then you need to provide a United States shipping address. If you're only purchasing digital products, then a United States shipping address isn't required."

##### 5.3.3 Price Adjustment
- **Node:** `price_adjustment_checkout`
- **Verbatim Message:**
  > "Thank you for shopping with Supresso Coffee.
  >
  > Kindly fill up below the information:
  > Username:
  > Screenshot:"

#### 5.4 Order issue (We are here to help your order issue)
- **Node:** `order_issue`

##### 5.4.1 Package
- **Node:** `package`
- **Verbatim Message:**
  > "Thank you for shopping with Supresso Coffee.
  >
  > Kindly fill up below the information:"

###### 5.4.1.1 I haven't received my order
- **Node:** `havent_received_order`

###### 5.4.1.2 Shipment issue
- **Node:** `shipment_issue`
- **Label:** "We are here to help your shipment issue"

- **Sub-node: Shipment**
  - **Node:** `shipment`
  - **Sub-node: Cannot add full address for shipment**
    - **Verbatim Message:**
      > "Thank you for shopping with Supresso Coffee."

##### 5.4.2 Information and partnership (What do you need?)
- **Node:** `information_and_partnership`

---

### BRANCH 6: Global Shipping
- **Node:** `global_shipping`
- **Verbatim Message (International shipping):**
  > "Most international orders will ship within 2 business days after placing your order. International transit times is vary based on destination but generally take between 7-15 business days depending on custom clearance. Please expect additional time for delivery during high-volume sale or holiday periods. Once your order has shipped, you will receive an email with a link.
  >
  > Shipping options:
  > DHL International Express
  > Ninja Van: Singapore, Malaysia, Indonesia only
  >
  > Should you have anymore questions, do not hesitate to contact us!"

#### 6.1 Shipping option
- **Node:** `shipping_option`
- **Verbatim Message (Complimentary Shipping):**
  > "Enjoy Complimentary Shipping on orders over S$30
  >
  > Shipping options: Ninjavan, J&T, QExpress
  >
  > Please allow 3 to 10 business days from the time you place your order to receive your package. During times of high order volume OR public holidays, extra time may be needed for your order to arrive
  >
  > Should you have anymore questions, do not hesitate to contact us!"

#### 6.2 Currency
- **Node:** `currency`
- **Verbatim Message:**
  > "We display and collect payment in your local currency. Please ensure you've selected your shipping destination in the country selector in the header to see prices and checkout in your local currency and payment methods."

#### 6.3 Taxes & Duties
- **Node:** `taxes_duties`
- **Verbatim Message:**
  > "Duties and taxes are included or paid at checkout for most countries.
  >
  > Singapore: Duties are included in the product price and taxes are paid at checkout for orders shipping to Singapore. You will not be charged additional duties and taxes at delivery."

---

### BRANCH 7: Find what you need
- **Node:** `find_what_you_need`
- **Children:** Kris+ Voucher, Shipping option

---

### BRANCH 8: Product recommendation (Coffee Variety)
- **Node:** `product_recommendation`
- **Label:** "Product recommendation (Coffee Variety)"

#### 8.1 Our houseblend coffee
- **Node:** `our_houseblend_coffee`
- **Verbatim Message:**
  > "Here are our recommendation: Equilibre Coffee, Espresso Coffee, Lumiere Coffee, Exotic Coffee
  >
  > Should you have anymore questions, do not hesitate to contact us!"

#### 8.2 Our arabica coffee
- **Node:** `our_arabica_coffee`
- **Verbatim Message:**
  > "Here are our recommendation: Luwak Coffee, Toraja Kalosi Coffee, Sumatra Mandheling Coffee, Flores Bajawa Coffee, Aceh Gayo Coffee, Signature Coffee.
  >
  > Should you have anymore questions, do not hesitate to contact us!"

#### 8.3 Our robusta coffee
- **Node:** `our_robusta_coffee`
- **Verbatim Message:**
  > "Here are our recommendation: Lampung Coffee, Nanyang Coffee, Ombre Coffee, West Java Coffee, Java Coffee
  >
  > Should you have anymore questions, do not hesitate to contact us!"

---

### BRANCH 9: Kris+ Voucher (Checkout)
- **Node:** `kris_voucher_detail`
- **Verbatim Message:**
  > "Redeem your Kris+ vouchers for an extra 20% OFF* exclusive on our website!
  >
  > T&C*
  > 1. This SGD 10 promo code must be redeemed by 30 June 2025.
  > 2. No extensions are allowed.
  > 3. Valid for redemption online at Supresso official website
  > 4. Promo codes are valid only for full-priced items and cannot be applied to discounted or promotional items, it is only valid for transactions above SGD30.
  > 5. Orders above SGD 30 will be entitled to free shipping.
  > 6. Limited to 1 redemption per order (one-time usage only)."

---

### BRANCH 10: Current Promotion (Coffee deals just for you ☕)
- **Node:** `current_promotion`
- **Children:**
  - Summer Sales → sesuain lagi promo dari IPL
  - Father's Day
  - Payday

---

### BRANCH 11: Information
- **Node:** `information`
- **Children:** Catalog, Information and partnership

#### 11.1 Catalog
- **Node:** `catalog`
- **Verbatim Message:**
  > "Kindly check our website https://www.supresso.com/ for your reference.
  >
  > Should you have any questions, please do not hesitate to contact us!"

#### 11.2 How to buy our coffee
- **Node:** `how_to_buy`
- **Children:**
  - Buy from Shopee
  - Buy from Lazada
  - Buy from Website

---

### BRANCH 12: Marketing
- **Node:** `marketing`

#### 12.1 Marketing & promotional information
- **Node:** `marketing_promotional_info`

##### 12.1.1 Email Marketing
- **Node:** `email_marketing`
- **Verbatim Message:**
  > "Scroll to the bottom of login page on the website or visit this page to subscribe to our email newsletter. As a subscriber, you'll be first to know about exclusive offers, product launches, and more.
  >
  > Please note that terms & conditions apply.
  >
  > Should you have anymore questions, do not hesitate to contact us!"

##### 12.1.2 WhatsApp Marketing
- **Node:** `whatsapp_marketing`
- **Verbatim Message:**
  > "Please send a WhatsApp message or reply "Unsubscribe" to +65 8792 0780
  >
  > Should you have anymore questions, do not hesitate to contact us!"

---

### BRANCH 13: Feedback
- **Node:** `feedback`

#### 13.1 Customer experience
- **Node:** `customer_experience`
- **Verbatim Message (Feedback form):**
  > "Thank you for shopping with Supresso Coffee.
  >
  > Kindly fill up below information:
  > Order no:
  > Best before date:
  >
  > We'll check and get back to you the soonest!"

##### 13.1.1 Product Care
- **Node:** `product_care`
- **Verbatim Message:**
  > "Store in a cool dry place away from direct sunlight and high temperatures.
  >
  > Please refrain from consuming the product if the packaging appears to be unsealed.
  >
  > Should you have anymore questions, do not hesitate to contact us!"

---

### SHARED NODE: Thank you for shopping with Supresso Coffee
- **Context:** Used as intermediate greeting in multiple branches (Checkout, Payment, Shipping, etc.)
- **Verbatim Message:**
  > "Thank you for shopping with Supresso Coffee."

---

### SHARED NODE: Thank you for shopping with Supresso Coffee (Shipping)
- **Context:** Shipping branch detail
- **Verbatim Message:**
  > "Thank you for shopping with Supresso Coffee.
  >
  > Kindly find below the information:"

---

## 3. Implementation Notes

1. **Brand Name:** Supresso Coffee (not Sugiura Coffee)
2. **Contact Details:**
   - Email: sg@supresso.com / adm_si@supresso.com
   - Phone: +65 8792 0780
   - Website: https://www.supresso.com/
3. **Currency:** SGD (Singapore Dollar)
4. **Marketplace Presence:** Website, Shopee, Lazada
5. **Dynamic Variable:** `{{system::customer_name}}` used in root greeting
6. **Kris+ Voucher:** 20% OFF, SGD 10 promo code, expires 30 June 2025
7. **Free Shipping Threshold:** SGD 30
8. **Shop Pay Installment Range:** 50 USD – 20,000 USD
9. **Minimum Wholesale Order:** 1 pallet (from Indonesia manufacturer)
10. **Shipping Options:** DHL International Express, Ninja Van (SG/MY/ID), Ninjavan, J&T, QExpress