@extends('layouts.app')

@section('title', 'Frequently Asked Questions (FAQ) — ShopBD')
@section('meta_description', 'Find answers to common questions about ordering, deliveries, Cash on Delivery, and returns at ShopBD.')

@section('content')
<div class="container py-5">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb small">
            <li class="breadcrumb-item"><a href="{{ route('home') }}" class="text-decoration-none text-muted">Home</a></li>
            <li class="breadcrumb-item active" aria-current="page">FAQ</li>
        </ol>
    </nav>

    <div class="row justify-content-center">
        <div class="col-lg-9">
            <div class="text-center mb-5">
                <h2 class="fw-bold">Frequently Asked Questions</h2>
                <p class="text-muted">Find quick answers to common questions regarding ordering, deliveries, and payment at ShopBD.</p>
            </div>

            <div class="accordion shadow-sm" id="faqAccordion">
                <!-- Question 1 -->
                <div class="accordion-item border-0 border-bottom">
                    <h2 class="accordion-header" id="headingOne">
                        <button class="accordion-button fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne">
                            1. How do I place an order on ShopBD?
                        </button>
                    </h2>
                    <div id="collapseOne" class="accordion-collapse collapse show" data-bs-parent="#faqAccordion">
                        <div class="accordion-body text-secondary small">
                            Browse products, choose your desired items, and click <strong>Add to Cart</strong> or <strong>Buy Now</strong>. Proceed to checkout, enter your delivery address and phone number, select Cash on Delivery, and click <strong>Place Order</strong>. You will receive an instant order confirmation SMS and tracking code.
                        </div>
                    </div>
                </div>

                <!-- Question 2 -->
                <div class="accordion-item border-0 border-bottom">
                    <h2 class="accordion-header" id="headingTwo">
                        <button class="accordion-button collapsed fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTwo">
                            2. What are the delivery charges and delivery times?
                        </button>
                    </h2>
                    <div id="collapseTwo" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                        <div class="accordion-body text-secondary small">
                            <ul>
                                <li><strong>Inside Dhaka City:</strong> ৳{{ number_format(\App\Models\Setting::get('inside_dhaka_delivery_fee', 60), 2) }} standard delivery fee. Orders typically arrive within 24 to 48 hours.</li>
                                <li><strong>Outside Dhaka (All 64 districts):</strong> ৳{{ number_format(\App\Models\Setting::get('outside_dhaka_delivery_fee', 120), 2) }} standard courier fee. Deliveries typically arrive within 3 to 5 business days.</li>
                                <li><strong>Free Delivery:</strong> Orders with cart subtotal exceeding ৳{{ number_format(\App\Models\Setting::get('free_shipping_threshold', 2500), 2) }} qualify for automatic free shipping.</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Question 3 -->
                <div class="accordion-item border-0 border-bottom">
                    <h2 class="accordion-header" id="headingThree">
                        <button class="accordion-button collapsed fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#collapseThree">
                            3. Is Cash on Delivery (COD) supported nationwide?
                        </button>
                    </h2>
                    <div id="collapseThree" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                        <div class="accordion-body text-secondary small">
                            Yes! We support 100% Cash on Delivery across all 64 districts and upazilas of Bangladesh. You pay the delivery courier in cash or via mobile payment (bKash/Nagad) once the package arrives at your doorstep.
                        </div>
                    </div>
                </div>

                <!-- Question 4 -->
                <div class="accordion-item border-0 border-bottom">
                    <h2 class="accordion-header" id="headingFour">
                        <button class="accordion-button collapsed fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFour">
                            4. Can I inspect the package before paying?
                        </button>
                    </h2>
                    <div id="collapseFour" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                        <div class="accordion-body text-secondary small">
                            Yes. You are encouraged to verify the outer box integrity and product match in the presence of the delivery courier. If the product received is damaged, defective, or incorrect, you may refuse acceptance immediately with zero charge.
                        </div>
                    </div>
                </div>

                <!-- Question 5 -->
                <div class="accordion-item border-0 border-bottom">
                    <h2 class="accordion-header" id="headingFive">
                        <button class="accordion-button collapsed fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFive">
                            5. How do I apply a discount coupon code?
                        </button>
                    </h2>
                    <div id="collapseFive" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                        <div class="accordion-body text-secondary small">
                            During checkout or in your shopping cart, type your promo code into the <strong>Have a Coupon?</strong> box and click <strong>Apply</strong>. The discount will instantly be deducted from your total bill according to the voucher terms.
                        </div>
                    </div>
                </div>

                <!-- Question 6 -->
                <div class="accordion-item border-0">
                    <h2 class="accordion-header" id="headingSix">
                        <button class="accordion-button collapsed fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#collapseSix">
                            6. What should I do if an item does not fit or is defective?
                        </button>
                    </h2>
                    <div id="collapseSix" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                        <div class="accordion-body text-secondary small">
                            We provide a hassle-free 7-day replacement and return guarantee. Simply call our customer helpline at <strong>{{ \App\Models\Setting::get('store_phone', '01700-000000') }}</strong> or send an email to <strong>{{ \App\Models\Setting::get('store_email', 'support@shopbd.com') }}</strong> with your order ID, and our team will arrange a free exchange or full refund.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
