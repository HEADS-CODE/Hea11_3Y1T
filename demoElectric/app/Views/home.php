<?= $this->extend('layout') ?> 
 
<?= $this->section('content') ?> 
 
<!-- Hero Section --> 
<section class="hero-section"> 
    <div class="container"> 
        <div class="row align-items-center"> 
            <div class="col-lg-6"> 
                <h1 class="display-4 fw-bold mb-4">Powering Your World with Excellence</h1> 
                <p class="lead mb-4">Professional electrical services you can trust. From residential wiring 
to commercial installations, we deliver safe, reliable, and efficient electrical solutions for over 25 
years.</p> 
                <div class="d-flex flex-wrap gap-3"> 
                    <a href="<?= base_url('services') ?>" class="btn btn-primary btn-lg">Our Services</a> 
                    <a href="<?= base_url('contact') ?>" class="btn btn-outline-light btn-lg">Get Quote</a> 
                </div> 
            </div> 
            <div class="col-lg-6 text-center"> 
                <div class="hero-image mt-5 mt-lg-0"> 
                    <i class="fas fa-bolt" style="font-size: 15rem; color: rgba(255,255,255,0.1);"></i> 

                </div> 
            </div> 
        </div> 
    </div> 
</section> 
 
<!-- Features Section --> 
<section class="section-padding bg-light-custom"> 
    <div class="container"> 
        <div class="row text-center mb-5"> 
            <div class="col-lg-8 mx-auto"> 
                <h2 class="display-5 fw-bold text-primary-custom mb-3">Why Choose Puihaha 
Electric?</h2> 
                <p class="lead text-muted">We combine decades of experience with cutting-edge 
technology to deliver exceptional electrical services that exceed expectations.</p> 
            </div> 
        </div> 
        <div class="row g-4"> 
            <div class="col-lg-4 col-md-6"> 
                <div class="card h-100 text-center p-4 feature-item"> 
                    <div class="feature-icon"> 
                        <i class="fas fa-shield-alt"></i> 
                    </div> 
                    <h4 class="text-primary-custom mb-3">Licensed & Insured</h4> 
                    <p class="text-muted">Fully licensed electricians with comprehensive insurance 
coverage for your peace of mind and protection.</p> 
                </div> 
            </div> 
            <div class="col-lg-4 col-md-6"> 
                <div class="card h-100 text-center p-4 feature-item"> 

                    <div class="feature-icon"> 
                        <i class="fas fa-clock"></i> 
                    </div> 
                    <h4 class="text-primary-custom mb-3">24/7 Emergency Service</h4> 
                    <p class="text-muted">Round-the-clock emergency electrical services because 
electrical problems don't wait for business hours.</p> 
                </div> 
            </div> 
            <div class="col-lg-4 col-md-6"> 
                <div class="card h-100 text-center p-4 feature-item"> 
                    <div class="feature-icon"> 
                        <i class="fas fa-award"></i> 
                    </div> 
                    <h4 class="text-primary-custom mb-3">25+ Years Experience</h4> 
                    <p class="text-muted">Over two decades of expertise in residential, commercial, and 
industrial electrical solutions.</p> 
                </div> 
            </div> 
            <div class="col-lg-4 col-md-6"> 
                <div class="card h-100 text-center p-4 feature-item"> 
                    <div class="feature-icon"> 
                        <i class="fas fa-tools"></i> 
                    </div> 
                    <h4 class="text-primary-custom mb-3">Modern Equipment</h4> 
                    <p class="text-muted">State-of-the-art tools and equipment ensure efficient, safe, and 
high-quality electrical work.</p> 
                </div> 
            </div> 
            <div class="col-lg-4 col-md-6"> 
                <div class="card h-100 text-center p-4 feature-item"> 

                    <div class="feature-icon"> 
                        <i class="fas fa-leaf"></i> 
                    </div> 
                    <h4 class="text-primary-custom mb-3">Eco-Friendly Solutions</h4> 
                    <p class="text-muted">Energy-efficient installations and solar solutions to reduce your 
carbon footprint and energy costs.</p> 
                </div> 
            </div> 
            <div class="col-lg-4 col-md-6"> 
                <div class="card h-100 text-center p-4 feature-item"> 
                    <div class="feature-icon"> 
                        <i class="fas fa-handshake"></i> 
                    </div> 
                    <h4 class="text-primary-custom mb-3">Satisfaction Guarantee</h4> 
                    <p class="text-muted">100% satisfaction guarantee on all our work with comprehensive 
warranties for your investment.</p> 
                </div> 
            </div> 
        </div> 
    </div> 
</section> 
 
<!-- Services Preview Section --> 
<section class="section-padding"> 
    <div class="container"> 
        <div class="row text-center mb-5"> 
            <div class="col-lg-8 mx-auto"> 
                <h2 class="display-5 fw-bold text-primary-custom mb-3">Our Core Services</h2> 
                <p class="lead text-muted">From simple repairs to complex installations, we handle all 
your electrical needs with precision and care.</p> 

            </div> 
        </div> 
        <div class="row g-4"> 
            <div class="col-lg-6"> 
                <div class="card h-100 p-4 feature-item"> 
                    <div class="row g-0 align-items-center"> 
                        <div class="col-md-3 text-center"> 
                            <div class="feature-icon mx-0"> 
                                <i class="fas fa-home"></i> 
                            </div> 
                        </div> 
                        <div class="col-md-9"> 
                            <h4 class="text-primary-custom mb-2">Residential Services</h4> 
                            <p class="text-muted mb-0">Complete home electrical solutions including wiring, 
panel upgrades, outlet installation, and smart home automation.</p> 
                        </div> 
                    </div> 
                </div> 
            </div> 
            <div class="col-lg-6"> 
                <div class="card h-100 p-4 feature-item"> 
                    <div class="row g-0 align-items-center"> 
                        <div class="col-md-3 text-center"> 
                            <div class="feature-icon mx-0"> 
                                <i class="fas fa-building"></i> 
                            </div> 
                        </div> 
                        <div class="col-md-9"> 
                            <h4 class="text-primary-custom mb-2">Commercial Services</h4> 

                            <p class="text-muted mb-0">Professional commercial electrical installations, 
maintenance, and emergency repairs for businesses of all sizes.</p> 
                        </div> 
                    </div> 
                </div> 
            </div> 
            <div class="col-lg-6"> 
                <div class="card h-100 p-4 feature-item"> 
                    <div class="row g-0 align-items-center"> 
                        <div class="col-md-3 text-center"> 
                            <div class="feature-icon mx-0"> 
                                <i class="fas fa-solar-panel"></i> 
                            </div> 
                        </div> 
                        <div class="col-md-9"> 
                            <h4 class="text-primary-custom mb-2">Solar Solutions</h4> 
                            <p class="text-muted mb-0">Sustainable energy solutions with solar panel 
installation, battery storage, and energy management systems.</p> 
                        </div> 
                    </div> 
                </div> 
            </div> 
            <div class="col-lg-6"> 
                <div class="card h-100 p-4 feature-item"> 
                    <div class="row g-0 align-items-center"> 
                        <div class="col-md-3 text-center"> 
                            <div class="feature-icon mx-0"> 
                                <i class="fas fa-exclamation-triangle"></i> 
                            </div> 

                        </div> 
                        <div class="col-md-9"> 
                            <h4 class="text-primary-custom mb-2">Emergency Repairs</h4> 
                            <p class="text-muted mb-0">24/7 emergency electrical repair services for power 
outages, electrical faults, and safety hazards.</p> 
                        </div> 
                    </div> 
                </div> 
            </div> 
        </div> 
        <div class="text-center mt-5"> 
            <a href="<?= base_url('services') ?>" class="btn btn-primary btn-lg">View All Services</a> 
        </div> 
    </div> 
</section> 
 
<!-- Statistics Section --> 
<section class="section-padding bg-primary text-white"> 
    <div class="container"> 
        <div class="row text-center"> 
            <div class="col-lg-3 col-md-6 mb-4"> 
                <div class="stat-item"> 
                    <h2 class="display-4 fw-bold text-secondary-custom mb-2">2500+</h2> 
                    <p class="lead mb-0">Projects Completed</p> 
                </div> 
            </div> 
            <div class="col-lg-3 col-md-6 mb-4"> 
                <div class="stat-item"> 
                    <h2 class="display-4 fw-bold text-secondary-custom mb-2">25+</h2> 

                    <p class="lead mb-0">Years Experience</p> 
                </div> 
            </div> 
            <div class="col-lg-3 col-md-6 mb-4"> 
                <div class="stat-item"> 
                    <h2 class="display-4 fw-bold text-secondary-custom mb-2">100%</h2> 
                    <p class="lead mb-0">Customer Satisfaction</p> 
                </div> 
            </div> 
            <div class="col-lg-3 col-md-6 mb-4"> 
                <div class="stat-item"> 
                    <h2 class="display-4 fw-bold text-secondary-custom mb-2">24/7</h2> 
                    <p class="lead mb-0">Emergency Service</p> 
                </div> 
            </div> 
        </div> 
    </div> 
</section> 
 
<!-- Call to Action Section --> 
<section class="section-padding bg-light-custom"> 
    <div class="container"> 
        <div class="row align-items-center"> 
            <div class="col-lg-8"> 
                <h2 class="display-5 fw-bold text-primary-custom mb-3">Ready to Power Up Your 
Project?</h2> 
                <p class="lead text-muted mb-4">Get a free consultation and quote for your electrical 
needs. Our expert team is ready to help you with safe, reliable, and efficient electrical 
solutions.</p> 
            </div> 

            <div class="col-lg-4 text-lg-end"> 
                <a href="<?= base_url('contact') ?>" class="btn btn-primary btn-lg me-3">Get Free 
Quote</a> 
                <a href="tel:5551234567" class="btn btn-outline-primary btn-lg"> 
                    <i class="fas fa-phone me-2"></i>Call Now 
                </a> 
            </div> 
        </div> 
    </div> 
</section> 
 
<?= $this->endSection() ?> 
 
Source Codes : about.php 
<?= $this->extend('layout') ?> 
 
<?= $this->section('content') ?> 
 
<!-- Page Header --> 
<section class="hero-section"> 
    <div class="container"> 
        <div class="row text-center"> 
            <div class="col-lg-8 mx-auto"> 
                <h1 class="display-4 fw-bold mb-4">About Puihaha Electric</h1> 
                <p class="lead">Powering communities with excellence, integrity, and innovation for over 
25 years</p> 
            </div> 
        </div> 
    </div> 

</section> 
 
<!-- Company Story Section --> 
<section class="section-padding"> 
    <div class="container"> 
        <div class="row align-items-center mb-5"> 
            <div class="col-lg-6"> 
                <h2 class="display-5 fw-bold text-primary-custom mb-4">Our Story</h2> 
                <p class="lead text-muted mb-4">Founded in 1999 by master electrician John Benedic R. 
Enriquez, Puihaha Electric began as a small family business with a simple mission: to provide safe, 
reliable, and affordable electrical services to our community.</p> 
                <p class="mb-4">What started as a one-man operation has grown into a trusted electrical 
contractor serving thousands of residential and commercial clients across the region. Our 
commitment to excellence, continuous learning, and customer satisfaction has been the driving 
force behind our success.</p> 
                <p class="mb-4">Today, Puihaha Electric stands as a testament to the power of hard work, 
dedication, and unwavering commitment to quality. We've built our reputation one project at a 
time, always putting safety and customer satisfaction first.</p> 
            </div> 
            <div class="col-lg-6"> 
                <div class="card border-0 shadow-lg"> 
                    <div class="card-body p-5 text-center"> 
                        <div class="feature-icon mb-4"> 
                            <i class="fas fa-history"></i> 
                        </div> 
                        <h3 class="text-primary-custom mb-3">25+ Years</h3> 
                        <p class="text-muted mb-0">of dedicated service to our community, building trust and 
delivering excellence in every project we undertake.</p> 
                    </div> 
                </div> 
            </div> 
        </div> 

    </div> 
</section> 
 
<!-- Mission, Vision, Values Section --> 
<section class="section-padding bg-light-custom"> 
    <div class="container"> 
        <div class="row text-center mb-5"> 
            <div class="col-lg-8 mx-auto"> 
                <h2 class="display-5 fw-bold text-primary-custom mb-3">Our Foundation</h2> 
                <p class="lead text-muted">The principles that guide everything we do</p> 
            </div> 
        </div> 
        <div class="row g-4"> 
            <div class="col-lg-4"> 
                <div class="card h-100 text-center p-4 feature-item"> 
                    <div class="feature-icon"> 
                        <i class="fas fa-bullseye"></i> 
                    </div> 
                    <h4 class="text-primary-custom mb-3">Our Mission</h4> 
                    <p class="text-muted">To provide safe, reliable, and innovative electrical solutions that 
exceed our customers' expectations while contributing to the sustainable development of our 
community.</p> 
                </div> 
            </div> 
            <div class="col-lg-4"> 
                <div class="card h-100 text-center p-4 feature-item"> 
                    <div class="feature-icon"> 
                        <i class="fas fa-eye"></i> 
                    </div> 

                    <h4 class="text-primary-custom mb-3">Our Vision</h4> 
                    <p class="text-muted">To be the leading electrical contractor in the region, recognized for 
our expertise, integrity, and commitment to advancing sustainable energy solutions.</p> 
                </div> 
            </div> 
            <div class="col-lg-4"> 
                <div class="card h-100 text-center p-4 feature-item"> 
                    <div class="feature-icon"> 
                        <i class="fas fa-heart"></i> 
                    </div> 
                    <h4 class="text-primary-custom mb-3">Our Values</h4> 
                    <p class="text-muted">Safety first, integrity always, excellence in execution, continuous 
learning, environmental responsibility, and unwavering commitment to customer satisfaction.</p> 
                </div> 
            </div> 
        </div> 
    </div> 
</section> 
 
<!-- Team Section --> 
<section class="section-padding"> 
    <div class="container"> 
        <div class="row text-center mb-5"> 
            <div class="col-lg-8 mx-auto"> 
                <h2 class="display-5 fw-bold text-primary-custom mb-3">Meet Our Expert Team</h2> 
                <p class="lead text-muted">Experienced professionals dedicated to delivering exceptional 
electrical services</p> 
            </div> 
        </div> 
        <div class="row g-4"> 

            <div class="col-lg-4 col-md-6"> 
                <div class="card h-100 text-center p-4 feature-item"> 
                    <div class="team-photo mb-4"> 
                        <div class="rounded-circle bg-primary d-flex align-items-center justify-content-center 
mx-auto" style="width: 120px; height: 120px;"> 
                            <i class="fas fa-user text-white" style="font-size: 3rem;"></i> 
                        </div> 
                    </div> 
                    <h4 class="text-primary-custom mb-2">John Benedic R. Enriquez</h4> 
                    <p class="text-secondary-custom fw-semibold mb-3">Founder & Master Electrician</p> 
                    <p class="text-muted small">With over 30 years of experience, John Benedic founded 
Puihaha Electric with a vision to provide exceptional electrical services. Licensed master 
electrician and certified in renewable energy systems.</p> 
                </div> 
            </div> 
            <div class="col-lg-4 col-md-6"> 
                <div class="card h-100 text-center p-4 feature-item"> 
                    <div class="team-photo mb-4"> 
                        <div class="rounded-circle bg-primary d-flex align-items-center justify-content-center 
mx-auto" style="width: 120px; height: 120px;"> 
                            <i class="fas fa-user text-white" style="font-size: 3rem;"></i> 
                        </div> 
                    </div> 
                    <h4 class="text-primary-custom mb-2">Sarah Martinez</h4> 
                    <p class="text-secondary-custom fw-semibold mb-3">Operations Manager</p> 
                    <p class="text-muted small">Sarah oversees daily operations and ensures every project 
meets our high standards. She brings 15 years of project management experience and holds a 
degree in Electrical Engineering.</p> 
                </div> 
            </div> 
            <div class="col-lg-4 col-md-6"> 

                <div class="card h-100 text-center p-4 feature-item"> 
                    <div class="team-photo mb-4"> 
                        <div class="rounded-circle bg-primary d-flex align-items-center justify-content-center 
mx-auto" style="width: 120px; height: 120px;"> 
                            <i class="fas fa-user text-white" style="font-size: 3rem;"></i> 
                        </div> 
                    </div> 
                    <h4 class="text-primary-custom mb-2">Michael Chen</h4> 
                    <p class="text-secondary-custom fw-semibold mb-3">Senior Electrician</p> 
                    <p class="text-muted small">Michael specializes in commercial installations and smart 
home automation. Licensed electrician with 12 years of experience and certified in advanced 
electrical systems and solar installations.</p> 
                </div> 
            </div> 
            <div class="col-lg-4 col-md-6"> 
                <div class="card h-100 text-center p-4 feature-item"> 
                    <div class="team-photo mb-4"> 
                        <div class="rounded-circle bg-primary d-flex align-items-center justify-content-center 
mx-auto" style="width: 120px; height: 120px;"> 
                            <i class="fas fa-user text-white" style="font-size: 3rem;"></i> 
                        </div> 
                    </div> 
                    <h4 class="text-primary-custom mb-2">Emily Rodriguez</h4> 
                    <p class="text-secondary-custom fw-semibold mb-3">Customer Service Manager</p> 
                    <p class="text-muted small">Emily ensures exceptional customer experience from initial 
contact to project completion. She coordinates scheduling, manages customer communications, 
and oversees quality assurance processes.</p> 
                </div> 
            </div> 
            <div class="col-lg-4 col-md-6"> 
                <div class="card h-100 text-center p-4 feature-item"> 

                    <div class="team-photo mb-4"> 
                        <div class="rounded-circle bg-primary d-flex align-items-center justify-content-center 
mx-auto" style="width: 120px; height: 120px;"> 
                            <i class="fas fa-user text-white" style="font-size: 3rem;"></i> 
                        </div> 
                    </div> 
                    <h4 class="text-primary-custom mb-2">David Thompson</h4> 
                    <p class="text-secondary-custom fw-semibold mb-3">Emergency Services Lead</p> 
                    <p class="text-muted small">David leads our 24/7 emergency response team. With 18 
years of experience in electrical troubleshooting and repairs, he ensures rapid response to 
electrical emergencies.</p> 
                </div> 
            </div> 
            <div class="col-lg-4 col-md-6"> 
                <div class="card h-100 text-center p-4 feature-item"> 
                    <div class="team-photo mb-4"> 
                        <div class="rounded-circle bg-primary d-flex align-items-center justify-content-center 
mx-auto" style="width: 120px; height: 120px;"> 
                            <i class="fas fa-user text-white" style="font-size: 3rem;"></i> 
                        </div> 
                    </div> 
                    <h4 class="text-primary-custom mb-2">Lisa Wang</h4> 
                    <p class="text-secondary-custom fw-semibold mb-3">Solar Solutions Specialist</p> 
                    <p class="text-muted small">Lisa heads our renewable energy division, specializing in 
solar panel installations and energy storage systems. Certified in NABCEP solar installation and 
energy efficiency consulting.</p> 
                </div> 
            </div> 
        </div> 
    </div> 
</section> 

 
<!-- Certifications & Awards Section --> 
<section class="section-padding bg-light-custom"> 
    <div class="container"> 
        <div class="row text-center mb-5"> 
            <div class="col-lg-8 mx-auto"> 
                <h2 class="display-5 fw-bold text-primary-custom mb-3">Certifications & 
Recognition</h2> 
                <p class="lead text-muted">Our commitment to excellence is recognized by industry 
leaders</p> 
            </div> 
        </div> 
        <div class="row g-4"> 
            <div class="col-lg-3 col-md-6"> 
                <div class="card h-100 text-center p-4 feature-item"> 
                    <div class="feature-icon"> 
                        <i class="fas fa-certificate"></i> 
                    </div> 
                    <h5 class="text-primary-custom mb-2">Licensed & Bonded</h5> 
                    <p class="text-muted small">State licensed electrical contractor with full bonding and 
insurance coverage</p> 
                </div> 
            </div> 
            <div class="col-lg-3 col-md-6"> 
                <div class="card h-100 text-center p-4 feature-item"> 
                    <div class="feature-icon"> 
                        <i class="fas fa-star"></i> 
                    </div> 
                    <h5 class="text-primary-custom mb-2">Better Business Bureau A+</h5> 

                    <p class="text-muted small">Accredited business with A+ rating for outstanding 
customer service</p> 
                </div> 
            </div> 
            <div class="col-lg-3 col-md-6"> 
                <div class="card h-100 text-center p-4 feature-item"> 
                    <div class="feature-icon"> 
                        <i class="fas fa-solar-panel"></i> 
                    </div> 
                    <h5 class="text-primary-custom mb-2">NABCEP Certified</h5> 
                    <p class="text-muted small">North American Board of Certified Energy Practitioners solar 
installation certification</p> 
                </div> 
            </div> 
            <div class="col-lg-3 col-md-6"> 
                <div class="card h-100 text-center p-4 feature-item"> 
                    <div class="feature-icon"> 
                        <i class="fas fa-trophy"></i> 
                    </div> 
                    <h5 class="text-primary-custom mb-2">Excellence Award 2023</h5> 
                    <p class="text-muted small">Regional Electrical Contractors Association Excellence in 
Service Award</p> 
                </div> 
            </div> 
        </div> 
    </div> 
</section> 
 
<!-- Company Timeline --> 
<section class="section-padding"> 

    <div class="container"> 
        <div class="row text-center mb-5"> 
            <div class="col-lg-8 mx-auto"> 
                <h2 class="display-5 fw-bold text-primary-custom mb-3">Our Journey</h2> 
                <p class="lead text-muted">Key milestones in our company's growth and development</p> 
            </div> 
        </div> 
        <div class="row"> 
            <div class="col-lg-8 mx-auto"> 
                <div class="timeline"> 
                    <div class="timeline-item mb-4"> 
                        <div class="row align-items-center"> 
                            <div class="col-md-3 text-md-end"> 
                                <h4 class="text-secondary-custom fw-bold">1999</h4> 
                            </div> 
                            <div class="col-md-1 text-center"> 
                                <div class="timeline-dot bg-primary rounded-circle" style="width: 20px; height: 
20px;"></div> 
                            </div> 
                            <div class="col-md-8"> 
                                <div class="card p-3"> 
                                    <h5 class="text-primary-custom mb-2">Company Founded</h5> 
                                    <p class="text-muted mb-0">John Benedic R. Enriquez establishes Puihaha 
Electric as a residential electrical service provider</p> 
                                </div> 
                            </div> 
                        </div> 
                    </div> 
                    <div class="timeline-item mb-4"> 

                        <div class="row align-items-center"> 
                            <div class="col-md-3 text-md-end"> 
                                <h4 class="text-secondary-custom fw-bold">2005</h4> 
                            </div> 
                            <div class="col-md-1 text-center"> 
                                <div class="timeline-dot bg-primary rounded-circle" style="width: 20px; height: 
20px;"></div> 
                            </div> 
                            <div class="col-md-8"> 
                                <div class="card p-3"> 
                                    <h5 class="text-primary-custom mb-2">Commercial Expansion</h5> 
                                    <p class="text-muted mb-0">Expanded services to include commercial and 
industrial electrical installations</p> 
                                </div> 
                            </div> 
                        </div> 
                    </div> 
                    <div class="timeline-item mb-4"> 
                        <div class="row align-items-center"> 
                            <div class="col-md-3 text-md-end"> 
                                <h4 class="text-secondary-custom fw-bold">2012</h4> 
                            </div> 
                            <div class="col-md-1 text-center"> 
                                <div class="timeline-dot bg-primary rounded-circle" style="width: 20px; height: 
20px;"></div> 
                            </div> 
                            <div class="col-md-8"> 
                                <div class="card p-3"> 
                                    <h5 class="text-primary-custom mb-2">Green Energy Initiative</h5> 

                                    <p class="text-muted mb-0">Launched solar installation services and renewable 
energy solutions</p> 
                                </div> 
                            </div> 
                        </div> 
                    </div> 
                    <div class="timeline-item mb-4"> 
                        <div class="row align-items-center"> 
                            <div class="col-md-3 text-md-end"> 
                                <h4 class="text-secondary-custom fw-bold">2018</h4> 
                            </div> 
                            <div class="col-md-1 text-center"> 
                                <div class="timeline-dot bg-primary rounded-circle" style="width: 20px; height: 
20px;"></div> 
                            </div> 
                            <div class="col-md-8"> 
                                <div class="card p-3"> 
                                    <h5 class="text-primary-custom mb-2">Smart Home Division</h5> 
                                    <p class="text-muted mb-0">Established smart home automation and IoT 
electrical solutions department</p> 
                                </div> 
                            </div> 
                        </div> 
                    </div> 
                    <div class="timeline-item"> 
                        <div class="row align-items-center"> 
                            <div class="col-md-3 text-md-end"> 
                                <h4 class="text-secondary-custom fw-bold">2024</h4> 
                            </div> 
                            <div class="col-md-1 text-center"> 

                                <div class="timeline-dot bg-secondary-custom rounded-circle" style="width: 20px; 
height: 20px;"></div> 
                            </div> 
                            <div class="col-md-8"> 
                                <div class="card p-3"> 
                                    <h5 class="text-primary-custom mb-2">25th Anniversary</h5> 
                                    <p class="text-muted mb-0">Celebrating 25 years of service with over 2,500 
completed projects</p> 
                                </div> 
                            </div> 
                        </div> 
                    </div> 
                </div> 
            </div> 
        </div> 
    </div> 
</section> 
 
<!-- Call to Action --> 
<section class="section-padding bg-primary text-white"> 
    <div class="container"> 
        <div class="row text-center"> 
            <div class="col-lg-8 mx-auto"> 
                <h2 class="display-5 fw-bold mb-4">Ready to Work with Us?</h2> 
                <p class="lead mb-4">Experience the Puihaha Electric difference. Contact us today for a 
free consultation and discover why thousands of customers trust us with their electrical 
needs.</p> 
                <div class="d-flex flex-wrap justify-content-center gap-3"> 
                    <a href="<?= base_url('contact') ?>" class="btn btn-secondary btn-lg">Get Free 
Quote</a> 

                    <a href="<?= base_url('services') ?>" class="btn btn-outline-light btn-lg">Our 
Services</a> 
                </div> 
            </div> 
        </div> 
    </div> 
</section> 
 
<?= $this->endSection() ?> 
 
Source Codes : contact.php 
<?= $this->extend('layout') ?> 
 
<?= $this->section('content') ?> 
 
<!-- Page Header --> 
<section class="hero-section"> 
    <div class="container"> 
        <div class="row text-center"> 
            <div class="col-lg-8 mx-auto"> 
                <h1 class="display-4 fw-bold mb-4">Contact Puihaha Electric</h1> 
                <p class="lead">Get in touch with our expert team for all your electrical needs. We're here 
to help 24/7.</p> 
            </div> 
        </div> 
    </div> 
</section> 
 
<!-- Contact Information --> 

<section class="section-padding"> 
    <div class="container"> 
        <div class="row g-4 mb-5"> 
            <div class="col-lg-3 col-md-6"> 
                <div class="card h-100 text-center p-4 feature-item"> 
                    <div class="feature-icon"> 
                        <i class="fas fa-map-marker-alt"></i> 
                    </div> 
                    <h4 class="text-primary-custom mb-3">Visit Our Office</h4> 
                    <p class="text-muted mb-0">123 Electric Avenue<br>Power City, PC 12345<br>United 
States</p> 
                </div> 
            </div> 
            <div class="col-lg-3 col-md-6"> 
                <div class="card h-100 text-center p-4 feature-item"> 
                    <div class="feature-icon"> 
                        <i class="fas fa-phone"></i> 
                    </div> 
                    <h4 class="text-primary-custom mb-3">Call Us</h4> 
                    <p class="text-muted mb-2"><strong>Main:</strong> (555) 123-4567</p> 
                    <p class="text-muted mb-0"><strong>Emergency:</strong> (555) 123-4567</p> 
                </div> 
            </div> 
            <div class="col-lg-3 col-md-6"> 
                <div class="card h-100 text-center p-4 feature-item"> 
                    <div class="feature-icon"> 
                        <i class="fas fa-envelope"></i> 
                    </div> 
                    <h4 class="text-primary-custom mb-3">Email Us</h4> 

                    <p class="text-muted mb
2"><strong>General:</strong><br>info@Puihahaelectric.com</p> 
                    <p class="text-muted mb
0"><strong>Emergency:</strong><br>emergency@Puihahaelectric.com</p> 
                </div> 
            </div> 
            <div class="col-lg-3 col-md-6"> 
                <div class="card h-100 text-center p-4 feature-item"> 
                    <div class="feature-icon"> 
                        <i class="fas fa-clock"></i> 
                    </div> 
                    <h4 class="text-primary-custom mb-3">Business Hours</h4> 
                    <p class="text-muted mb-2"><strong>Mon-Fri:</strong> 7:00 AM - 6:00 PM</p> 
                    <p class="text-muted mb-2"><strong>Saturday:</strong> 8:00 AM - 4:00 PM</p> 
                    <p class="text-muted mb-0"><strong>Emergency:</strong> 24/7</p> 
                </div> 
            </div> 
        </div> 
    </div> 
</section> 
 
<!-- Contact Form Section --> 
<section class="section-padding bg-light-custom"> 
    <div class="container"> 
        <div class="row"> 
            <div class="col-lg-8 mx-auto"> 
                <div class="card shadow-lg border-0"> 
                    <div class="card-body p-5"> 
                        <div class="text-center mb-4"> 

                            <h2 class="display-6 fw-bold text-primary-custom mb-3">Get Your Free Quote</h2> 
                            <p class="lead text-muted">Fill out the form below and we'll get back to you within 24 
hours with a detailed quote for your project.</p> 
                        </div> 
                         
                        <?php if (isset($success) && $success): ?> 
                            <div class="alert alert-success alert-dismissible fade show" role="alert"> 
                                <i class="fas fa-check-circle me-2"></i><?= $success ?> 
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button> 
                            </div> 
                        <?php endif; ?> 
                         
                        <?php if (isset($error) && $error): ?> 
                            <div class="alert alert-danger alert-dismissible fade show" role="alert"> 
                                <i class="fas fa-exclamation-circle me-2"></i><?= $error ?> 
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button> 
                            </div> 
                        <?php endif; ?> 
                         
                        <form method="POST" action="<?= base_url('contact') ?>" id="contactForm"> 
                            <?= csrf_field() ?> 
                             
                            <div class="row g-3"> 
                                <div class="col-md-6"> 
                                    <label for="name" class="form-label fw-semibold">Full Name *</label> 
                                    <input type="text" class="form-control form-control-lg <?= 
isset($validation['name']) ? 'is-invalid' : '' ?>"  
                                           id="name" name="name" value="<?= old('name') ?>" required> 
                                    <?php if (isset($validation['name'])): ?> 

                                        <div class="invalid-feedback"><?= $validation['name'] ?></div> 
                                    <?php endif; ?> 
                                </div> 
                                <div class="col-md-6"> 
                                    <label for="email" class="form-label fw-semibold">Email Address *</label> 
                                    <input type="email" class="form-control form-control-lg <?= 
isset($validation['email']) ? 'is-invalid' : '' ?>"  
                                           id="email" name="email" value="<?= old('email') ?>" required> 
                                    <?php if (isset($validation['email'])): ?> 
                                        <div class="invalid-feedback"><?= $validation['email'] ?></div> 
                                    <?php endif; ?> 
                                </div> 
                                <div class="col-md-6"> 
                                    <label for="phone" class="form-label fw-semibold">Phone Number *</label> 
                                    <input type="tel" class="form-control form-control-lg <?= 
isset($validation['phone']) ? 'is-invalid' : '' ?>"  
                                           id="phone" name="phone" value="<?= old('phone') ?>" required> 
                                    <?php if (isset($validation['phone'])): ?> 
                                        <div class="invalid-feedback"><?= $validation['phone'] ?></div> 
                                    <?php endif; ?> 
                                </div> 
                                <div class="col-md-6"> 
                                    <label for="service_type" class="form-label fw-semibold">Service Type *</label> 
                                    <select class="form-select form-select-lg <?= isset($validation['service_type']) ? 
'is-invalid' : '' ?>"  
                                            id="service_type" name="service_type" required> 
                                        <option value="">Select a service...</option> 
                                        <option value="residential_wiring" <?= old('service_type') == 'residential_wiring' 
? 'selected' : '' ?>>Residential Wiring</option> 

                                        <option value="commercial_electrical" <?= old('service_type') == 
'commercial_electrical' ? 'selected' : '' ?>>Commercial Electrical</option> 
                                        <option value="panel_upgrade" <?= old('service_type') == 'panel_upgrade' ? 
'selected' : '' ?>>Panel Upgrade</option> 
                                        <option value="lighting_installation" <?= old('service_type') == 
'lighting_installation' ? 'selected' : '' ?>>Lighting Installation</option> 
                                        <option value="smart_home" <?= old('service_type') == 'smart_home' ? 
'selected' : '' ?>>Smart Home Automation</option> 
                                        <option value="solar_installation" <?= old('service_type') == 'solar_installation' ? 
'selected' : '' ?>>Solar Installation</option> 
                                        <option value="ev_charging" <?= old('service_type') == 'ev_charging' ? 'selected' 
: '' ?>>EV Charging Station</option> 
                                        <option value="emergency_repair" <?= old('service_type') == 'emergency_repair' 
? 'selected' : '' ?>>Emergency Repair</option> 
                                        <option value="maintenance" <?= old('service_type') == 'maintenance' ? 
'selected' : '' ?>>Maintenance Service</option> 
                                        <option value="other" <?= old('service_type') == 'other' ? 'selected' : '' 
?>>Other</option> 
                                    </select> 
                                    <?php if (isset($validation['service_type'])): ?> 
                                        <div class="invalid-feedback"><?= $validation['service_type'] ?></div> 
                                    <?php endif; ?> 
                                </div> 
                                <div class="col-12"> 
                                    <label for="message" class="form-label fw-semibold">Project Details *</label> 
                                    <textarea class="form-control <?= isset($validation['message']) ? 'is-invalid' : '' ?>"  
                                              id="message" name="message" rows="5"  
                                              placeholder="Please describe your electrical project, including location, 
timeline, and any specific requirements..." required><?= old('message') ?></textarea> 
                                    <?php if (isset($validation['message'])): ?> 
                                        <div class="invalid-feedback"><?= $validation['message'] ?></div> 
                                    <?php endif; ?> 

                                </div> 
                                <div class="col-12"> 
                                    <div class="form-check"> 
                                        <input class="form-check-input" type="checkbox" id="consent" required> 
                                        <label class="form-check-label text-muted" for="consent"> 
                                            I agree to be contacted by Puihaha Electric regarding my inquiry and 
understand that my information will be kept confidential. 
                                        </label> 
                                    </div> 
                                </div> 
                                <div class="col-12 text-center"> 
                                    <button type="submit" class="btn btn-primary btn-lg px-5"> 
                                        <i class="fas fa-paper-plane me-2"></i>Send Message 
                                    </button> 
                                </div> 
                            </div> 
                        </form> 
                    </div> 
                </div> 
            </div> 
        </div> 
    </div> 
</section> 
 
<!-- Emergency Contact Section --> 
<section class="section-padding bg-danger text-white"> 
    <div class="container"> 
        <div class="row text-center"> 
            <div class="col-lg-8 mx-auto"> 

                <h2 class="display-5 fw-bold mb-4"> 
                    <i class="fas fa-exclamation-triangle text-warning me-3"></i>Electrical Emergency? 
                </h2> 
                <p class="lead mb-4">Don't wait! Electrical emergencies can be dangerous. Call our 24/7 
emergency hotline for immediate assistance.</p> 
                <div class="d-flex flex-wrap justify-content-center gap-3"> 
                    <a href="tel:5551234567" class="btn btn-warning btn-lg"> 
                        <i class="fas fa-phone me-2"></i>Emergency: (555) 123-4567 
                    </a> 
                    <a href="mailto:emergency@Puihahaelectric.com" class="btn btn-outline-light btn-lg"> 
                        <i class="fas fa-envelope me-2"></i>Emergency Email 
                    </a> 
                </div> 
                <div class="mt-4"> 
                    <p class="mb-0"><small>Available 24 hours a day, 7 days a week for electrical 
emergencies</small></p> 
                </div> 
            </div> 
        </div> 
    </div> 
</section> 
 
<!-- Service Areas --> 
<section class="section-padding"> 
    <div class="container"> 
        <div class="row text-center mb-5"> 
            <div class="col-lg-8 mx-auto"> 
                <h2 class="display-5 fw-bold text-primary-custom mb-3">Service Areas</h2> 
                <p class="lead text-muted">We proudly serve the following areas with professional 
electrical services</p> 

            </div> 
        </div> 
        <div class="row g-4"> 
            <div class="col-lg-4 col-md-6"> 
                <div class="card h-100 text-center p-4 feature-item"> 
                    <div class="feature-icon"> 
                        <i class="fas fa-city"></i> 
                    </div> 
                    <h4 class="text-primary-custom mb-3">Metropolitan Area</h4> 
                    <ul class="list-unstyled text-muted"> 
                        <li>Power City</li> 
                        <li>Electric Heights</li> 
                        <li>Voltage Valley</li> 
                        <li>Current Creek</li> 
                    </ul> 
                </div> 
            </div> 
            <div class="col-lg-4 col-md-6"> 
                <div class="card h-100 text-center p-4 feature-item"> 
                    <div class="feature-icon"> 
                        <i class="fas fa-home"></i> 
                    </div> 
                    <h4 class="text-primary-custom mb-3">Suburban Communities</h4> 
                    <ul class="list-unstyled text-muted"> 
                        <li>Residential Ridge</li> 
                        <li>Family Fields</li> 
                        <li>Suburban Springs</li> 
                        <li>Neighborhood Nook</li> 
                    </ul> 

                </div> 
            </div> 
            <div class="col-lg-4 col-md-6"> 
                <div class="card h-100 text-center p-4 feature-item"> 
                    <div class="feature-icon"> 
                        <i class="fas fa-industry"></i> 
                    </div> 
                    <h4 class="text-primary-custom mb-3">Industrial Zones</h4> 
                    <ul class="list-unstyled text-muted"> 
                        <li>Manufacturing Mile</li> 
                        <li>Industrial Park</li> 
                        <li>Business Boulevard</li> 
                        <li>Commerce Center</li> 
                    </ul> 
                </div> 
            </div> 
        </div> 
        <div class="text-center mt-5"> 
            <p class="text-muted">Don't see your area listed? <a href="#contactForm" class="text
primary-custom fw-semibold">Contact us</a> to check if we service your location.</p> 
        </div> 
    </div> 
</section> 
 
<!-- Map Section (Placeholder) --> 
<section class="section-padding bg-light-custom"> 
    <div class="container"> 
        <div class="row text-center mb-4"> 
            <div class="col-lg-8 mx-auto"> 

                <h2 class="display-5 fw-bold text-primary-custom mb-3">Find Us</h2> 
                <p class="lead text-muted">Visit our office or call to schedule an on-site consultation</p> 
            </div> 
        </div> 
        <div class="row"> 
            <div class="col-12"> 
                <div class="card"> 
                    <div class="card-body p-0"> 
                        <!-- In a real application, you would embed Google Maps or another map service --> 
                        <div class="bg-secondary d-flex align-items-center justify-content-center text-white" 
style="height: 400px;"> 
                            <div class="text-center"> 
                                <i class="fas fa-map-marked-alt mb-3" style="font-size: 4rem;"></i> 
                                <h4>Interactive Map</h4> 
                                <p class="mb-0">123 Electric Avenue, Power City, PC 12345</p> 
                                <a href="https://maps.google.com" target="_blank" class="btn btn-primary mt-3"> 
                                    <i class="fas fa-directions me-2"></i>Get Directions 
                                </a> 
                            </div> 
                        </div> 
                    </div> 
                </div> 
            </div> 
        </div> 
    </div> 
</section> 
 
<script> 
document.addEventListener('DOMContentLoaded', function() { 

    // Form validation 
    const form = document.getElementById('contactForm'); 
    const submitBtn = form.querySelector('button[type="submit"]'); 
     
    form.addEventListener('submit', function(e) { 
        // Add loading state 
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Sending...'; 
        submitBtn.disabled = true; 
    }); 
     
    // Phone number formatting 
    const phoneInput = document.getElementById('phone'); 
    phoneInput.addEventListener('input', function(e) { 
        let value = e.target.value.replace(/\D/g, ''); 
        if (value.length >= 6) { 
            value = value.replace(/(\d{3})(\d{3})(\d{4})/, '($1) $2-$3'); 
        } else if (value.length >= 3) { 
            value = value.replace(/(\d{3})(\d{0,3})/, '($1) $2'); 
        } 
        e.target.value = value; 
    }); 
}); 
</script> 
 
<?= $this->endSection() ?>