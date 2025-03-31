
	<!-- Start Footer Area -->
	<footer class="footer">
    <!-- Footer Top -->
    <div class="footer-top section">
        <div class="container">
            <div class="row align-items-start">
                <div class="col-lg-8 col-md-7 col-12">
                    <!-- About Widget -->
                    <div class="single-footer about">
                        <div class="logo">
                            <a href="/">
                                <img src="/frontend/img/jaya%20logo.png" alt="Jaya Studio Logo" class="footer-logo">
                            </a>
                        </div>
                        <p class="text">Kepercayaan Anda adalah prioritas kami. Kami hadir untuk memberikan pengalaman belanja terbaik, karena setiap pilihan Anda adalah langkah menuju kepuasan.</p>
                        <p class="call">Ada Pertanyaan? Hubungi kami 24/7 <span><a href="https://wa.me/+6281238465833">+62 812-3846-5833</a></span></p>
                    </div>
                </div>
                <div class="col-lg-4 col-md-5 col-12">
                    <!-- Social/Contact Widget -->
                    <div class="single-footer social">
                        <h4>Lebih Dekat</h4>
                        <div class="contact">
						<ul class="contact-list">
                                <li><i class="fa fa-map-marker"></i> H947+5WJ, Cempaga, Bangli, Bali 80613</li>
                                <li><i class="fa fa-envelope"></i> jayastudio@gmail.com</li>
                                <li><i class="fa fa-phone"></i> +62 812-3846-5833</li>
                            </ul>
                            </ul>
                        </div>
                        <div class="social-links">
                            <p>Ikuti kami:</p>
                            <a href="https://www.facebook.com/oman.sidembunut" target="_blank" title="Facebook" class="social-icon">
                                <img src="https://img.icons8.com/ios-filled/30/ffffff/facebook.png" alt="Facebook">
                            </a>
                            <a href="https://www.instagram.com/jaya_hangdrum/" target="_blank" title="Instagram" class="social-icon">
                                <img src="https://img.icons8.com/ios-filled/30/ffffff/instagram-new.png" alt="Instagram">
                            </a>
                            <a href="https://github.com/Radityana64/Jaya-app" target="_blank" title="GitHub" class="social-icon">
                                <img src="https://img.icons8.com/ios-filled/30/ffffff/github.png" alt="GitHub">
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Footer Bottom -->
    <div class="footer-bottom">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6 col-12">
                    <div class="copyright-text text-lg-left text-center">
                        <p>Copyright © {{ date('Y') }} <a href="https://github.com/Radityana64/Jaya-app" target="_blank">Jaya Studio</a> - All Rights Reserved.</p>
                    </div>
                </div>
                <div class="col-lg-6 col-12">
                    <div class="right text-lg-right text-center">
                        <!-- Add additional links or content here if needed -->
                    </div>
                </div>
            </div>
        </div>
    </div>
</footer>
<style>
	.footer-top.section {
    padding: 40px 0; /* Consistent padding */
}

.footer-logo {
    width: 150px; /* Fixed width for consistency */
    height: auto;
    margin-bottom: 20px;
}
</style>
	<!-- /End Footer Area -->
 
	<!-- Jquery -->
    <script src="{{asset('frontend/js/jquery.min.js')}}"></script>
    <script src="{{asset('frontend/js/jquery-migrate-3.0.0.js')}}"></script>
	<script src="{{asset('frontend/js/jquery-ui.min.js')}}"></script>
	<!-- Popper JS -->
	<script src="{{asset('frontend/js/popper.min.js')}}"></script>
	<!-- Bootstrap JS -->
	<script src="{{asset('frontend/js/bootstrap.min.js')}}"></script>
	<!-- Color JS -->
	<!-- <script src="{{asset('frontend/js/colors.js')}}"></script> -->
	<!-- Slicknav JS -->
	<script src="{{asset('frontend/js/slicknav.min.js')}}"></script>
	<!-- Owl Carousel JS -->
	<script src="{{asset('frontend/js/owl-carousel.js')}}"></script>
	<!-- Magnific Popup JS -->
	<script src="{{asset('frontend/js/magnific-popup.js')}}"></script>
	<!-- Waypoints JS -->
	<script src="{{asset('frontend/js/waypoints.min.js')}}"></script>
	<!-- Countdown JS -->
	<script src="{{asset('frontend/js/finalcountdown.min.js')}}"></script>
	<!-- Nice Select JS -->
	<!-- <script src="{{asset('frontend/js/nicesellect.js')}}"></script> -->
	<!-- Flex Slider JS -->
	<script src="{{asset('frontend/js/flex-slider.js')}}"></script>
	<!-- ScrollUp JS -->
	<script src="{{asset('frontend/js/scrollup.js')}}"></script>
	<!-- Onepage Nav JS -->
	<script src="{{asset('frontend/js/onepage-nav.min.js')}}"></script>
	{{-- Isotope --}}
	<script src="{{asset('frontend/js/isotope/isotope.pkgd.min.js')}}"></script>
	<!-- Easing JS -->
	<script src="{{asset('frontend/js/easing.js')}}"></script>

	<!-- Active JS -->
	<script src="{{asset('frontend/js/active.js')}}"></script>

	<!-- jQuery -->
<!-- <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script> -->
	@stack('scripts')
	<!-- <script>
		setTimeout(function(){
		  $('.alert').slideUp();
		},5000);
		$(function() {
		// ------------------------------------------------------- //
		// Multi Level dropdowns
		// ------------------------------------------------------ //
			$("ul.dropdown-menu [data-toggle='dropdown']").on("click", function(event) {
				event.preventDefault();
				event.stopPropagation();

				$(this).siblings().toggleClass("show");


				if (!$(this).next().hasClass('show')) {
				$(this).parents('.dropdown-menu').first().find('.show').removeClass("show");
				}
				$(this).parents('li.nav-item.dropdown.show').on('hidden.bs.dropdown', function(e) {
				$('.dropdown-submenu .show').removeClass("show");
				});

			});
		});
	  </script> -->