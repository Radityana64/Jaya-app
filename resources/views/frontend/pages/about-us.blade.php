@extends('frontend.layouts.master')

@section('title','E-SHOP || About Us')

@section('main-content')

	<!-- Breadcrumbs -->
	<div class="breadcrumbs">
		<div class="container">
			<div class="row">
				<div class="col-12">
					<div class="bread-inner">
						<ul class="bread-list">
							<li><a href="index1.html">Beranda<i class="ti-arrow-right"></i></a></li>
							<li class="active"><a href="blog-single.html">Tentang Jaya Studio</a></li>
						</ul>
					</div>
				</div>
			</div>
		</div>
	</div>
	<!-- End Breadcrumbs -->

	<!-- About Us -->
	<section class="about-us section">
		<div class="container">
			<div class="row">
				<div class="col-lg-6 col-12">
					<div class="about-content animate-element">
					<h2 class="animate-up">Selamat Datang di <span>Jaya Studio</span> - Spesialis Handpan & Perkusi</h2>
					<p class="animate-up">Didirikan dengan penuh passion, Jaya Studio telah berkembang menjadi destinasi utama untuk instrumen perkusi unik yang berkualitas tinggi. Kami mengkhususkan diri pada handpan, hang drum, serta beragam pilihan instrumen perkusi lainnya dengan menghadirkan suara yang penuh keunikan.</p>

					<p class="animate-up">Dedikasi pasti diberikan oleh setiap tempaan bahan untuk menjadikan instrumen yang memukau. Keahlian ini memungkinkan kami untuk memilih hanya instrumen terbaik yang memenuhi ekspetasi pelanggan untuk kualitas nada, kerajinan tangan, dan daya tahan.</p>
						<div class="button animate-up">
							<a href="/etalase/produk" class="btn">Belanja Sekarang</a>
						</div>
						
						<div class="social-links mt-4 animate-up">
							<a href="https://www.instagram.com/jaya_hangdrum/" class="social-icon"><i class="fa fa-instagram fa-2x mr-3"></i></a>
							<a href="https://www.facebook.com/oman.sidembunut" class="social-icon"><i class="fa fa-facebook fa-2x mr-3"></i></a>
							<a href="https://www.youtube.com/@jayanyomane9046" class="social-icon"><i class="fa fa-youtube fa-2x mr-3"></i></a>
							<a href="https://wa.me/6285737131598" class="social-icon"><i class="fa fa-whatsapp fa-2x"></i></a>
						</div>
					</div>
				</div>
				<div class="col-lg-6 col-12">
					<div class="about-img animate-element animate-right">
						<img src="{{ asset('/frontend/img/about1.jpeg') }}" alt="Handpan Instrument" class="img-fluid">
					</div>
				</div>
			</div>
		</div>
	</section>
	<!-- End About Us -->
	
	<!-- Our Mission -->
	<section class="our-mission section bg-gray">
		<div class="container">
			<div class="row">
				<div class="col-12">
					<div class="section-title animate-element">
						<h2 class="animate-up">Misi Kami</h2>
						<p class="animate-up">Kami percaya pada kekuatan transformatif musik dan kemampuannya untuk akulturasi budaya dan media untuk mendukung kreativitas serta aktivitas.</p>
					</div>
				</div>
			</div>
			<div class="row">
				<div class="col-lg-4 col-md-6 col-12">
					<div class="single-feature animate-element animate-up">
						<div class="icon-head"><i class="fa fa-music"></i></div>
						<h4>Instrumen Berkualitas</h4>
						<p>Kami memastikan setiap pembuatan instrumen yang unggul dan kualitas suara luar biasa untuk menginspirasi perjalanan musik Anda.</p>
					</div>
				</div>
				<div class="col-lg-4 col-md-6 col-12">
					<div class="single-feature animate-element animate-up" data-delay="200">
						<div class="icon-head"><i class="fa fa-users"></i></div>
						<h4>Panduan Pemain Instrumen</h4>
						<p>Pemilik selaku musisi berpengalaman memberikan pemahaman mengenai musik yang membantu Anda menemukan instrumen yang  sesuai dengan gaya bermain dan aspirasi musik Anda.</p>
					</div>
				</div>
				<div class="col-lg-4 col-md-6 col-12">
					<div class="single-feature animate-element animate-up" data-delay="400">
						<div class="icon-head"><i class="fa fa-globe"></i></div>
						<h4>Apresiasi Budaya</h4>
						<p>Kami mendorong pemahaman dan rasa ingin tahu terhadap permasinan alat musik, serta membagikan bagaimana alat musik yang indah ini dapat dimainkan.</p>
					</div>
				</div>
			</div>
		</div>
	</section>
	<!-- End Our Mission -->
	
	<!-- Our Collection -->
	<section class="shop-services section">
		<div class="container">
			<div class="row">
				<div class="col-lg-6 col-md-6 col-12">
					<div class="showcase-item animate-element">
						<div class="image-container">
							<img src="{{ asset('/frontend/img/about5.jpg') }}" alt="Musisi memainkan handpan" class="img-fluid">
							<div class="overlay-hover"></div>
						</div>
						<div class="showcase-overlay">
							<h4>Pilihan Alat Musik</h4>
							<p>Pilih instrumen yang membuat nyaman dan memberikan kenangan</p>
						</div>
					</div>
				</div>
				<div class="col-lg-6 col-md-6 col-12">
					<div class="showcase-item animate-element" data-delay="200">
						<div class="image-container">
							<img src="{{ asset('/frontend/img/about3.jpeg') }}" alt="Koleksi instrumen perkusi" class="img-fluid">
							<div class="overlay-hover"></div>
						</div>
						<div class="showcase-overlay">
							<h4>Keunggulan Buatan Tangan</h4>
							<p>Setiap instrumen adalah karya seni dengan karakter suara unik</p>
						</div>
					</div>
				</div>
			</div>
		</div>
	</section>
	<!-- End Our Collection -->
	
	<!-- Our Story -->
	<section class="our-story section">
		<div class="container">
			<div class="row">
				<div class="col-12">
					<div class="section-title animate-element">
						<h2 class="animate-up">Kisah Kami</h2>
						<p class="animate-up">Perjalanan yang didorong oleh dedikasi terhadap alat musik perkusi berkualitas</p>
					</div>
				</div>
			</div>
			<div class="row">
				<div class="col-lg-8 offset-lg-2 col-12">
					<div class="story-content animate-element">
						<p class="animate-up">Perjalanan kami dimulai pada tahun 2015, ketika I Nyoman Adi Suardita, seorang seniman asal Bali, mendirikan Jaya Studio. Berawal dari kecintaannya terhadap musik dan alat musik perkusi, beliau mulai memproduksi alat musik pertama, yaitu hang drum, di rumahnya di Bangli, Bali.</p>
						
						<p class="animate-up" data-delay="200">Setelah bertahun-tahun belajar dan menyempurnakan tekniknya, Pak Nyoman akhirnya berhasil memproduksi alat musik berkualitas yang siap dijual pada tahun 2017. Dengan keterbatasan alat dan tenaga kerja, produksi saat itu masih dilakukan secara mandiri. Baru pada tahun 2019, Jaya Studio mulai berkembang dengan merekrut beberapa karyawan dan meningkatkan kualitas bahan serta proses produksi.</p>
						
						<p class="animate-up" data-delay="400">Saat ini, Jaya Studio telah memiliki tempat produksi sendiri yang masih berada di lingkungan rumah Pak Nyoman. Proses pembuatan alat musik kini dibantu oleh mesin untuk tahap awal, namun sentuhan akhir seperti pembentukan nada dan pola tetap dilakukan secara manual demi menjaga kualitas terbaik. Produk yang telah selesai dipajang di ruang khusus, di mana pelanggan dapat langsung memilih alat musik yang mereka inginkan.</p>
						
						<blockquote class="animate-element animate-up" data-delay="600">
							"Musik adalah bahasa universal yang menyatukan jiwa-jiwa dari berbagai latar belakang. Kami menghadirkan alat musik yang menjadi jembatan bagi harmoni tersebut."
						</blockquote>
						
						<p class="animate-up" data-delay="800">Kami mengundang Anda untuk mengunjungi Jaya Studio, merasakan langsung keunikan alat musik kami, dan menjadi bagian dari komunitas musisi yang terus berkembang.</p>
					</div>
				</div>
			</div>
			<div class="row mt-4 justify-content-center text-center">
				<div class="col-12">
					<div class="section-title animate-element">
						<h2 class="animate-up">Hubungi Kami</h2>
						<div class="social mt-4 animate-up">
							<a href="https://www.instagram.com/jaya_hangdrum/" class="social-icon"><i class="fa fa-instagram fa-2x mr-3"></i></a>
							<a href="https://www.facebook.com/oman.sidembunut" class="social-icon"><i class="fa fa-facebook fa-2x mr-3"></i></a>
							<a href="https://www.youtube.com/@jayanyomane9046" class="social-icon"><i class="fa fa-youtube fa-2x mr-3"></i></a>
							<a href="https://wa.me/6285737131598" class="social-icon"><i class="fa fa-whatsapp fa-2x"></i></a>
						</div>
					</div>
				</div>
			</div>
		</div>
	</section>
	<!-- End Our Story -->
	 
	<!-- Store Location -->
	<section class="store-location section bg-gray">
		<div class="container">
			<div class="row">
				<div class="col-12">
					<div class="section-title animate-element">
						<h2 class="animate-up">Kunjungi Toko</h2>
						<p class="animate-up">Datang Ke Tempat Alat Musik</p>
					</div>
				</div>
			</div>
			<div class="row">
				<div class="col-lg-5 col-md-5 col-12">
					<div class="location-info animate-element animate-up">
						<div class="location-details">
							<div class="icon-box">
								<i class="fa fa-map-marker"></i>
							</div>
							<h4>Lokasi</h4>
							<p>Jl. Nusantara, Br. Sidembunut, Kel. Cempaga, Kec. Bangli, Kab. Bangli, Prov. Bali</p>
						</div>
						<div class="contact-details mt-4">
							<div class="icon-box">
								<i class="fa fa-clock-o"></i>
							</div>
							<h4>Buka Pukul</h4>
							<p>Senin - Sabtu: 09:00 - 19:00 WITA<br>Sunday: By Appointment</p>
						</div>
						<div class="directions mt-4">
							<a href="https://maps.app.goo.gl/H9j18PMygiE9h8Yy7" target="_blank" class="btn">Cari Alamat</a>
						</div>
					</div>
				</div>
				<div class="col-lg-7 col-md-7 col-12">
					<div class="map-container animate-element animate-right">
					<iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d1973.281276180749!2d115.36238104105!3d-8.44454460943925!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x2dd219b33b34f071%3A0x9737e2887e7115e!2sJaya%20Studio%20(Balinese%20Music%20Instrument)!5e0!3m2!1sen!2sid!4v1742123769191!5m2!1sen!2sid" width="100%" height="400" style="border:0; border-radius:8px;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
					</div>
				</div>
			</div>
		</div>
	</section>
	<!-- End Store Location -->

	<!-- Custom CSS -->
	<style>
		.directions .btn {
			color: white !important;
		}
		/* Animation Classes */
		.animate-element {
			opacity: 0;
			transition: all 0.8s ease;
		}
		
		.animate-element.show {
			opacity: 1;
		}
		
		.animate-up {
			transform: translateY(50px);
			opacity: 0;
			transition: all 0.8s ease;
		}
		
		.animate-up.show {
			transform: translateY(0);
			opacity: 1;
		}
		
		.animate-right {
			transform: translateX(50px);
			opacity: 0;
			transition: all 0.8s ease;
		}
		
		.animate-right.show {
			transform: translateX(0);
			opacity: 1;
		}
		
		/* Social Icons Styling */
		.social-links {
			display: flex;
			align-items: center;
		}
		
		.social-icon {
			color: #333;
			transition: all 0.3s ease;
		}
		
		.social-icon:hover {
			color: #797979;
			transform: scale(1.1);
		}
		
		/* Image Container with Overlay Effect */
		.showcase-item {
			position: relative;
			margin-bottom: 30px;
			overflow: hidden;
			border-radius: 8px;
			box-shadow: 0 5px 15px rgba(0,0,0,0.1);
		}
		
		.image-container {
			height: 300px;
			overflow: hidden;
			position: relative;
		}
		
		.image-container img {
			width: 100%;
			height: 100%;
			object-fit: cover;
			transition: transform 0.8s ease;
		}
		
		.overlay-hover {
			position: absolute;
			top: 0;
			left: 0;
			width: 100%;
			height: 100%;
			background: rgba(0,0,0,0);
			transition: all 0.5s ease;
		}
		
		.showcase-item:hover .overlay-hover {
			background: rgba(0,0,0,0.2);
		}
		
		.showcase-item:hover .image-container img {
			transform: scale(1.05);
		}
		
		.showcase-overlay {
			position: absolute;
			bottom: 0;
			left: 0;
			right: 0;
			background: rgba(0,0,0,0.7);
			color: white;
			padding: 20px;
			transform: translateY(100%);
			transition: all 0.5s ease;
		}
		
		.showcase-item:hover .showcase-overlay {
			transform: translateY(0);
		}
		
		.showcase-overlay h4 {
			color: white;
			margin-bottom: 10px;
		}
		
		/* About Content Enhancement */
		.about-content h2 {
			margin-bottom: 25px;
			font-size: 32px;
			font-weight: 700;
		}
		
		.about-content h2 span {
			color: #000000;
		}
		
		.about-content p {
			margin-bottom: 20px;
			line-height: 1.8;
		}
		
		/* Story Section Enhancement */
		.story-content {
			text-align: center;
			padding: 30px;
			background: #fff;
			border-radius: 8px;
			box-shadow: 0 5px 15px rgba(0,0,0,0.05);
		}
		
		.story-content blockquote {
			font-style: italic;
			color: #000000;
			font-size: 18px;
			padding: 15px 30px;
			margin: 20px 0;
			border-left: 5px solid #797979;
			background: #f9f9f9;
		}
		
		/* Section transitions */
		.section {
			position: relative;
			overflow: hidden;
		}
		
		.section::before {
			content: '';
			position: absolute;
			top: 0;
			left: 0;
			width: 100%;
			height: 100%;
			background-color: rgba(255,255,255,0.9);
			transform: translateX(-100%);
			transition: transform 0.8s ease;
			z-index: -1;
		}
		
		.section.show::before {
			transform: translateX(0);
		}
	</style>

	<!-- Animation JavaScript -->
	<script>
		document.addEventListener('DOMContentLoaded', function() {
			// Only trigger animations on scroll, not on page load
			
			// Scroll animations
			function checkScroll() {
				var elements = document.querySelectorAll('.animate-element:not(.show), .animate-up:not(.show), .animate-right:not(.show), .section:not(.show)');
				var windowHeight = window.innerHeight;
				
				elements.forEach(function(element) {
					var positionFromTop = element.getBoundingClientRect().top;
					var delay = element.getAttribute('data-delay') || 0;
					
					// Only animate when element is visible in viewport
					if (positionFromTop - windowHeight <= -50 && positionFromTop + 100 >= 0) {
						setTimeout(function() {
							element.classList.add('show');
						}, delay);
					}
				});
			}
			
			// Check on scroll
			window.addEventListener('scroll', checkScroll);
			// Initial check after a small delay to allow page to render
			setTimeout(checkScroll, 100);
		});
	</script>

@endsection