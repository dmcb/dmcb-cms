<?php

class Patch extends MY_Controller {

	function Patch()
	{
		parent::__construct();
	}
	
	/* Patches from dmcb cms 1 to dmcb cms 4 */
	
	function database_upgrade()
	{
		$directory_pages = array();	
		$directory_posts = array();
		$existing_users = array();
		$article_categories = array();	
		
		// Create new pages + roles
		$this->db->query("INSERT INTO acls_roles (role, internal, custom) VALUES (".
			"'condo doc contributor',".
			"'0',".
			"'1')");
		$condo_doc_contributor_roleid = $this->db->insert_id();
		
		$this->db->query("ALTER TABLE  `pages` AUTO_INCREMENT =0");
		$this->db->query("INSERT INTO `pages` (`menu`, `title`, `content`, `pageof`, `imageid`, `datemodified`, `link`, `published`, `protected`, `position`, `urlname`, `needsubscription`, `pagepostname`, `page_templateid`, `post_templateid`) VALUES
			('main', 'Account', NULL, NULL, NULL, '2010-08-26 13:43:40', '/account', 1, 1, 1, NULL, 0, 0, NULL, NULL),
			('main', 'Manage security', NULL, 1, NULL, '2010-08-26 13:38:58', '/manage_security', 1, 1, 2, NULL, 0, 0, NULL, NULL),
			('main', 'Manage users', NULL, 1, NULL, '2010-08-26 13:40:36', '/manage_users', 1, 1, 3, NULL, 0, 0, NULL, NULL),
			('main', 'Manage pages', NULL, 1, NULL, '2010-08-26 13:42:06', '/manage_pages', 1, 1, 4, NULL, 0, 0, NULL, NULL),
			('main', 'Manage content', NULL, 1, NULL, '2010-08-26 13:42:28', '/manage_content', 1, 1, 5, NULL, 0, 0, NULL, NULL),
			('main', 'Manage activity', NULL, 1, NULL, '2010-08-26 13:42:52', '/manage_activity', 1, 1, 6, NULL, 0, 0, NULL, NULL),
			('main', 'Sign off', NULL, 1, NULL, '2010-10-10 11:14:18', '/signoff', 1, 0, 7, NULL, 0, 0, NULL, NULL);");

		$this->db->query("INSERT INTO pages (page_templateid, menu, title, datemodified, published, position, urlname, content) VALUES (".
			"'3',".
			"'nomenu',".
			"'Welcome',".
			"NOW(),".
			"'1',".
			"'1',".
			"'welcome',".
			$this->db->escape("<div style=\"float: right; width: 400px;\"> 
					<div class=\"feature\"> 
						<h3>Gerald's latest featured listings</h3> 
						%block_latest_listings%
					</div> 
 
					<div class=\"feature\"> 
						<h3>Gerald's condo directory additions</h3> 
						%block_latest_directory%
					</div> 
				</div>
 
				<p style=\"font-size: 18pt; width: 516px;\"> 
					<i>\"Have my expertise on <u>your</u> side<br/>when you sell or buy a Calgary condo home!\"</i><br/> 
				</p> 
				<p style=\"text-align: right; padding-right: 10px; font-size: 14pt; width: 516px;\"> 
					-Gerald, 403-703-0675
				</p> 
 
				<img src=\"/file/page/welcome/plugs.gif\" alt=\"\" />").")");
		
		$this->db->query("INSERT INTO pages (pageid, menu, title, datemodified, published, position) VALUES (".
			"'9',".
			"'main',".
			"'About Gerald',".
			"NOW(),".
			"'1',".
			"'2')");
			
		$this->db->query("INSERT INTO pages (pageid, menu, title, datemodified, published, position) VALUES (".
			"'10',".
			"'main',".
			"'Serving You',".
			"NOW(),".
			"'1',".
			"'3')");
			
		$this->db->query("INSERT INTO pages (pageid, menu, title, datemodified, published, position) VALUES (".
			"'11',".
			"'main',".
			"'Articles',".
			"NOW(),".
			"'1',".
			"'4')");
			
		$this->db->query("INSERT INTO pages (pageid, menu, title, datemodified, published, position) VALUES (".
			"'12',".
			"'main',".
			"'Condos',".
			"NOW(),".
			"'1',".
			"'5')");

		$this->db->query("INSERT INTO pages (page_templateid, pageof, menu, title, datemodified, published, position, urlname, content) VALUES (".
			"'3',".
			"'9',".
			"'main',".
			"'About Gerald',".
			"NOW(),".
			"'1',".
			"'1',".
			"'about',".
			$this->db->escape("<table> 
				<tr> 
					<td> 
						<img src=\"/file/page/about/gerald.jpg\" style=\"margin: 10px;\" alt=\"\"/> 
					</td> 
					<td> 
						<h2>Gerald Rotering</h2> 
						<p>Gerald is a Realtor and a Certified Condominium Specialist (\"CCS\") with extensive condominium and civic government experience, and is a professional member of the Canadian Condominium Institute (CCI). See further credentials and bio notes below. The \"CCS\" designation is issued by the Calgary Real Estate Board as Canada's first program of condominium training and certification in the real estate industry.</p> 
					</td> 
				</tr> 
			</table> 
			<hr/> 
 
			<h2>Gerald's Background</h2> 
			<table> 
				<tr> 
					<td> 
						<ul> 
							<li>President the past ten years of the 40-suite condominium corporation where I live and own five condo suites in Calgary’s Connaught neighborhood.</li> 
							<li>Author of Contemplating Condominiums, the feature article in Calgary's Condo Guide magazine. Some articles are also reprinted on the Calgary Herald's housing web site. The Condo Guide appears every fourth week, and is free at every major grocery store in Calgary, plus all Mac's Milk locations.</li> 
							<li>Author of columns in the Calgary Real Estate News, the weekly publication of the Calgary Real Estate Board.</li> 
							<li>Professional member of the Canadian Condominium Institute (CCI).</li> 
							<li>Graduate of numerous condominium courses, including the CCI’s three-level Condo Management series.</li> 
							<li>My earlier background includes several terms as a small-city Mayor, Alderman, Economic Development Chairman and chairing a civic Police Board. I also served as assistant to a Member of Parliament and two Members of the B.C. Legislative Assembly.</li> 
							<li>Real estate course instructor for the Alberta Real Estate Association.</li> 
						</ul> 
					</td> 
					<td> 
						<img src=\"/file/page/about/about_view.jpg\" alt=\"\" style=\"margin: 10px;\"/><br/> 
						Downtown Calgary, seen from Gerald's home office.
					</td> 
				</tr> 
			</table>").")");
			
		$this->db->query("INSERT INTO pages (page_templateid, pageof, menu, title, datemodified, published, position, urlname, content) VALUES (".
			"'3',".			
			"'9',".
			"'main',".
			"'What People are Saying',".
			"NOW(),".
			"'1',".
			"'2',".
			"'testimonials',".
			$this->db->escape("<p>Here are some of the comments clients have written to me after my work with them in home purchase or sale. The originals are available for review - just ask.</p> 
 
			<table class=\"entry\"> 
				<tr> 
					<td style=\"padding: 10px;\"> 
						Thanks for all your help in regards to purchasing my first condo.  I will definitely have to recommend you to my friends...  It's been a great experience, and I have learned a lot during this time.  Dealing with you was a pleasure and I didn't feel pressured at all during this.
					</td> 
				</tr> 
				<tr> 
					<td style=\"text-align: right; padding: 10px;\"> 
						-Warren M.
					</td> 
				</tr> 
			</table>		
			
			<table class=\"entry\"> 
				<tr> 
					<td style=\"padding: 10px;\"> 
						I was overwhelmed by your attention to all the details, and your speedy expertise in my home purchase. You were extra-ordinary! Thank you so much for everything, and for bringing a gift (when you were sick!!).
					</td> 
				</tr> 
				<tr> 
					<td style=\"text-align: right; padding: 10px;\"> 
						-Eleanor S.
					</td> 
				</tr> 
			</table> 
 
			<table class=\"entry\"> 
				<tr> 
					<td style=\"padding: 10px;\"> 
						Thank you, Gerald, for everything! You made our lives so much easier. This sale/purchase was the easiest I have ever experienced. Come for a visit soon!
					</td> 
				</tr> 
				<tr> 
					<td style=\"text-align: right; padding: 10px;\"> 
						-Donna C.
					</td> 
				</tr> 
			</table> 
 
			<table class=\"entry\"> 
				<tr> 
					<td style=\"padding: 10px;\"> 
						I have been happy with your services from the first time we met on the phone, right up to when you met me at the condo with the gift basket. You are sincere and never pressuring, and I hope that my friends, as well as myself, will be able to use your services in the future. Thank you for all your help.
					</td> 
				</tr> 
				<tr> 
					<td style=\"text-align: right; padding: 10px;\"> 
						-Vince R.
					</td> 
				</tr> 
			</table> 
 
			<table class=\"entry\"> 
				<tr> 
					<td style=\"padding: 10px;\"> 
						There’s nothing you could have done better. We would recommend your services to family and friends. We would tell them that you use the buyer’s (or seller’s) time efficiently, prompt responses with buyer/seller inquiries, and a pressure-free environment for making decisions. We are enjoying condo life!
					</td> 
				</tr> 
				<tr> 
					<td style=\"text-align: right; padding: 10px;\"> 
						-Neil and Stacey H.
					</td> 
				</tr> 
			</table> 
 
			<table class=\"entry\"> 
				<tr> 
					<td style=\"padding: 10px;\"> 
						You provided excellent service. You are a friendly, patient, well-organized and trustworthy Realtor who provides honest advice and always thinks of your clients’ needs and interest. I wouldn’t hesitate to recommend you. Should I ever decide to sell, you’ll be the first person I call. Thanks for your help and patience!
					</td> 
				</tr> 
				<tr> 
					<td style=\"text-align: right; padding: 10px;\"> 
						-Paul B.
					</td> 
				</tr> 
			</table> 
 
			<table class=\"entry\"> 
				<tr> 
					<td style=\"padding: 10px;\"> 
						If there is one Realtor who is at the top of his game, it is Gerald Rotering. Gerald went “the extra mile” during our house deal, and stayed very late with the seller and her Realtor to pound out an agreement. Gerald was impressive! He took care of everything, from arriving early to show us homes, to giving us a welcome-home basket with champagne! Gerald, you didn’t miss a beat! You will definitely be referred to our friends and family!
					</td> 
				</tr> 
				<tr> 
					<td style=\"text-align: right; padding: 10px;\"> 
						-Paul and Gina R.
					</td> 
				</tr> 
			</table> 
 
			<table class=\"entry\"> 
				<tr> 
					<td style=\"padding: 10px;\"> 
						We would refer (friends) to you because of your experience, hard work, and honesty about the situation. You let your clients know if their expectations are realistic or not; this is important to us. We really appreciated your help in finding a mortgage specialist and a lawyer--without this help we may not have received a mortgage…the bank refused us.
					</td> 
				</tr> 
				<tr> 
					<td style=\"text-align: right; padding: 10px;\"> 
						-Scott and Mike.
					</td> 
				</tr> 
			</table>").")");
			
		$this->db->query("INSERT INTO pages (page_templateid, pageof, menu, title, datemodified, published, position, urlname, content) VALUES (".
			"'3',".
			"'9',".
			"'main',".
			"'Calgary Links',".
			"NOW(),".
			"'1',".
			"'3',".
			"'links',".
			$this->db->escape("<table> 
				<tr> 
					<td colspan=\"2\"> 
						<h2>Condominium and real estate links</h2> 
					</td> 
				</tr> 
				<tr> 
					<td> 
						<ul> 
							<li><a href=\"http://www.cci-south.ab.ca/\" target=\"_blank\">Canadian Condominium Institute, South Alberta Chapter</a></li> 
							<li><a href=\"http://www.cci.ca/\" target=\"_blank\">Canadian Condominium Institute, National</a></li> 
							<li><a href=\"http://www.reca.ca/\" target=\"_blank\">Real Estate Council of Alberta (regulatory authority)</a></li> 
							<li><a href=\"http://www.creb.com/\" target=\"_blank\">Calgary Real Estate Board (local professional association for Realtors)</a></li> 
						</ul> 
					</td> 
					<td> 
						<ul> 
							<li><a href=\"http://www.abrea.ab.ca/\" target=\"_blank\">Alberta Real Estate Association (province-wide professional association for Realtors)</a></li> 
							<li><a href=\"http://www.rentfaster.ca/\" target=\"_blank\">Rental web site</a></li> 
							<li><a href=\"http://www.condoserve.com/\" target=\"_blank\">CondoServe (national condo information; for-profit site)</a></li> 
						</ul> 
					</td> 
				</tr> 
				<tr> 
					<td> 
						<h2>Attractions</h2> 
						<ul> 
							<li><a href=\"http://www.atplive.com/\" target=\"_blank\">Alberta Theatre Projects</a></li> 
							<li><a href=\"http://www.calawaypark.com/\" target=\"_blank\">Calaway Park</a></li> 
							<li><a href=\"http://www.calgaryscience.ca/\" target=\"_blank\">Calgary Science Centre</a></li> 
							<li><a href=\"http://calgarystampede.com/\" target=\"_blank\">Calgary Stampede</a></li> 
							<li><a href=\"http://www.calgarytower.com/\" target=\"_blank\">Calgary Tower</a></li> 
							<li><a href=\"http://www.calgaryzoo.org/\" target=\"_blank\">Calgary Zoo</a></li> 
							<li><a href=\"http://www.canadaolympicpark.ca/\" target=\"_blank\">Canada Olympic Park</a></li> 
							<li><a href=\"http://www.fortcalgary.ab.ca/\" target=\"_blank\">Fort Calgary</a></li> 
							<li><a href=\"http://www.glenbow.org/\" target=\"_blank\">Glenbow Museum</a></li> 
							<li><a href=\"http://www.heritagepark.ab.ca/\" target=\"_blank\">Heritage Park</a></li> 
							<li><a href=\"http://www.pioneeracres.ab.ca/\" target=\"_blank\">Pioneer Acres</a></li> 
							<li><a href=\"http://www.racecity.com/\" target=\"_blank\">Race City Motorsport</a></li> 
							<li><a href=\"http://www.sprucemeadows.com/\" target=\"_blank\">Spruce Meadows</a></li> 
						</ul> 
 
						<h2>Business</h2> 
						<ul> 
							<li><a href=\"http://www.calgarychamber.com/\" target=\"_blank\">Calgary Chamber of Commerce</a></li> 
						</ul> 
 
						<h2>Education</h2> 
						<ul> 
							<li><a href=\"http://www.acad.ab.ca/\" target=\"_blank\">Alberta College of Art and Design</a></li> 
							<li><a href=\"http://www.cbe.ab.ca/\" target=\"_blank\">Calgary Board of Education</a></li> 
							<li><a href=\"http://www.mtroyal.ab.ca/\" target=\"_blank\">Mount Royal University</a></li> 
							<li><a href=\"http://www.sait.ab.ca/\" target=\"_blank\">SAIT</a></li> 
							<li><a href=\"http://www.ucalgary.ca/\" target=\"_blank\">University of Calgary</a></li> 
							<li><a href=\"http://www.cssd.ab.ca/\" target=\"_blank\">Calgary Catholic School Board</a></li> 
						</ul> 
 
						<h2>Government</h2> 
						<ul> 
							<li><a href=\"http://www.calgary.ca/\" target=\"_blank\">City of Calgary</a></li> 
							<li><a href=\"http://www.calgarypolice.ca/\" target=\"_blank\">Calgary Police Service</a></li> 
							<li><a href=\"http://content.calgary.ca/CCA/City+Hall/Business+Units/Calgary+Fire+Department/index.htm\" target=\"_blank\">Calgary Fire Department</a></li> 
							<li><a href=\"http://www.gov.ab.ca/\" target=\"_blank\">Government of Alberta</a></li> 
							<li><a href=\"http://www.canada.gc.ca/\" target=\"_blank\">Government of Canada</a></li> 
						</ul> 
 
						<h2>Recreation</h2> 
						<ul> 
							<li><a href=\"http://www.parkscanada.ca/\" target=\"_blank\">Parks Canada</a></li> 
							<li><a href=\"http://www.explorealberta.com/\" target=\"_blank\">Travel Alberta</a></li> 
						</ul> 
 
						<h2>Health care</h2> 
						<ul> 
							<li><a href=\"http://www.crha-health.ab.ca/\" target=\"_blank\">Calgary Regional Health Authority</a></li> 
							<li><a href=\"http://www.stars.ca/home.asp\" target=\"_blank\">STARS Air Ambulance</a></li> 
						</ul> 
 
					</td> 
					<td> 
						<h2>Local information</h2> 
						<ul> 
							<li><a href=\"http://www.calgaryattractions.com/\" target=\"_blank\">Calgary Attractions</a></li> 
							<li><a href=\"http://www.discovercalgary.com/\" target=\"_blank\">Discover Calgary</a></li> 
							<li><a href=\"http://www.tourismcalgary.com/\" target=\"_blank\">Tourism Calgary</a></li> 
						</ul> 
 
						<h2>Newspaper</h2> 
						<ul> 
							<li><a href=\"http://www.canada.com/calgary/calgaryherald\" target=\"_blank\">Calgary Herald</a></li> 
							<li><a href=\"http://www.calgarysun.com/\" target=\"_blank\">Calgary Sun</a></li> 
							<li><a href=\"http://www.theglobeandmail.com/\" target=\"_blank\">The Globe and Mail</a></li> 
						</ul> 
 
						<h2>Radio</h2> 
						<ul> 
							<li><a href=\"http://www.cjay92.com/\" target=\"_blank\">CJAY 92</a></li> 
							<li><a href=\"http://www.cjsw.com/\" target=\"_blank\">CJSW</a></li> 
							<li><a href=\"http://www.country105.com/\" target=\"_blank\">Country 105</a></li> 
							<li><a href=\"http://www.lite96.com/\" target=\"_blank\">Light 96 FM</a></li> 
							<li><a href=\"http://www.qr77.com/\" target=\"_blank\">QR 77</a></li> 
							<li><a href=\"http://www.cbc.ca/radio/\" target=\"_blank\">CBC Radio Calgary</a></li> 
						</ul> 
 
						<h2>Sports</h2> 
						<ul> 
							<li><a href=\"http://www.calgaryflames.com/\" target=\"_blank\">Calgary Flames</a></li> 
							<li><a href=\"http://www.hitmenhockey.com/\" target=\"_blank\">Calgary Hitmen</a></li> 
							<li><a href=\"http://www.stampeders.com/\" target=\"_blank\">Calgary Stampeders</a></li> 
						</ul> 
 
						<h2>Television</h2> 
						<ul> 
							<li><a href=\"http://www.citytv.com/calgary\" target=\"_blank\">CityTV</a></li> 
							<li><a href=\"http://www.canada.com/globaltv/index.html\" target=\"_blank\">Global</a></li> 
							<li><a href=\"http://www.cfcn.ca/\" target=\"_blank\">CFCN</a></li> 
							<li><a href=\"http://www.cbc.ca/television\" target=\"_blank\">CBC Television - Calgary</a></li> 
						</ul> 
 
						<h2>Transportation</h2> 
						<ul> 
							<li><a href=\"http://www.ama.ab.ca/\" target=\"_blank\">Alberta Motor Association</a></li> 
							<li><a href=\"http://www.calgarytransit.com/\" target=\"_blank\">Calgary Transit</a></li> 
							<li><a href=\"http://www.calgaryairport.com/\" target=\"_blank\">Calgary Airport Authority</a></li> 
						</ul> 
 
						<h2>Weather</h2> 
						<ul> 
							<li><a href=\"http://www.weatheroffice.ec.gc.ca/\" target=\"_blank\">Environment Canada Calgary</a></li> 
						</ul> 
 
					</td> 
				</tr> 
			</table>").")");
			
		$this->db->query("INSERT INTO pages (page_templateid, pageof, menu, title, datemodified, published, position, urlname, content) VALUES (".
			"'3',".
			"'10',".
			"'main',".
			"'Serving Buyers',".
			"NOW(),".
			"'1',".
			"'1',".
			"'buyers',".
			$this->db->escape("<h2>A consumer-protection approach to serving condo buyers</h2> 

			<p>Whether you’re a first-time home buyer or a veteran, you’ll appreciate my team’s condominium specialized knowledge. Taking a consumer-protection approach on behalf of the buying client, we’ll find the home you need and review the condominium documents with you. Our thorough approach will make you confident of your decision when you proceed with the purchase.</p> 

			<ul> 
				<li> 
					Specializing in first-time buyers, we offer patience and an educational approach. Every condo shopper receives a comprehensive condo information kit.
				</li> 

				<li> 
					Consumer protection is the priority. We warn of buildings and condominium corporations that have difficulties. We’ll assist with--or obtain an independent--condo documents review.
				</li> 

				<li> 
					Every buyer is welcomed home with a housewarming gift on his or her possession day.
				</li> 

				<li> 
					After-sale service for condo buyers! Our clients are clients for life. We provide my clients with on-going advice if they have questions in future about the operation of their condominium community, and regarding the changing market value of their homes.
				</li> 

				<li> 
					Home-buyer service agreements between real estate associates and their clients are recommended by the Real Estate Council of Alberta. <a href=\"/file/page/buyers/buyer_service_agreement.pdf\">Read the agreement under which I serve my home-shopping clients.</a> 
				</li> 
			</ul> 

			<p><a href=\"/site/view/1\">Read more about my consumer-protection approach for condominium home buyers.</a></p>").")");
			
		$this->db->query("INSERT INTO pages (page_templateid, pageof, menu, title, datemodified, published, position, urlname, content) VALUES (".
			"'3',".
			"'10',".
			"'main',".
			"'Serving Sellers',".
			"NOW(),".
			"'1',".
			"'2',".
			"'sellers',".
			$this->db->escape("<h2>Sellers can win, using CondosInCalgary.com!</h2> 
 
			<p>Your condo apartment or townhouse home could sell faster, and at full market value, if you have me apply my skills and use this web site to market and sell your property.  Your edge comes in part from the thousands of Calgary condominium shoppers who are reading this site regularly for its terrific content and condo listings.  Check out the advantages of working with me, then give me a call to discuss how I can sell your condo home!</p> 
 
			<p>-Gerald  403-703-0675</p> 
 
			<br/> 
			
			<ul> 
				<li><p>Reduced cost to sellers when I represent the buyer as well as the seller.</p></li> 
				<li><p>Marketing via www.CondosInCalgary.com, known across Canada as a great source for Calgary condo listings, the Calgary Condo Directory, condo information, and handy links.</p></li> 
				<li><p>Advertising your home in the Calgary Real Estate News weekly newspaper, available free everywhere in Calgary.</p></li> 
				<li><p>Colour feature sheets for your home on display in your property, with wide-angle-lens photography and professionally-written text.</p></li> 
				<li><p>MLS listing with double-checked information, professional photography and well-written comments.</p></li> 
				<li><p>Direct contact with me!  Rather than hiding behind a wall of receptionists, answering services, and assistants, my clients call and speak with me directly.</p></li> 
				<li><p>Review with you of disclosures that will keep you out of trouble after the sale closes. I know condominiums, and can discuss any issues in your home and in the building.  We can work to solve the issue, or disclose it, if that will keep a disappointed seller from suing you after taking possession.  Not every issue has to compromise your sale price.</p></li> 
				<li><p>Sale contract clauses written to serve you.  Sellers’ terms and conditions are often ignored in sale negotiations, but I make sure your concerns are covered when an offer is received.</p></li> 
			</ul> ").")");
			
		$this->db->query("INSERT INTO pages (pageof, menu, title, datemodified, published, position, link) VALUES (".
			"'11',".
			"'main',".
			"'10 Condo Tips',".
			"NOW(),".
			"'1',".
			"'1',".
			"'/buying-condominiums-and-buyer-protection/post/my-ten-tips-for-condominium-purchasers')");
			
		$this->db->query("INSERT INTO pages (pageof, menu, title, datemodified, published, position, link) VALUES (".
			"'11',".
			"'main',".
			"'Seniors\' Condos',".
			"NOW(),".
			"'1',".
			"'2',".
			"'/seniors-condos/post/condo-options-for-seniors')");
			
		$this->db->query("INSERT INTO pages (page_templateid, pageof, menu, title, datemodified, published, position, urlname, content) VALUES (".
			"'3',".
			"'11',".
			"'main',".
			"'Browse Articles',".
			"NOW(),".
			"'1',".
			"'3',".
			"'articles',".
			$this->db->escape("<table> 
				<tr> 
					<td style=\"vertical-align: top; width: 200px;\"> 
						<div class=\"sidebar\"> 
							<h3>Search</h3> 
							<form action=\"/search\" method=\"post\">			
								%block_form%
								<input name=\"searchtext\" type=\"text\" class=\"text\" /> 
								<input type=\"submit\" value=\"Go\" name=\"search\" class=\"button\"/> 
							</form> 
						</div class=\"sidebar\"> 
						<div class=\"sidebar\"> 
							<h3>Article Sections</h3> 
							%block_article_categories%
						</td> 
					<td style=\"vertical-align: top; width: 20px;\">&nbsp;</td> 
					<td style=\"vertical-align: top; \"> 
						<h2>Condo information for owners, sellers &amp; buyers</h2> 
			<p> 
				Are you thinking about buying a condominium? Are you looking for more information about Condominium living? Contemplating Condominiums is a monthly feature in Calgary condominium magazines, and sometimes appears in the Calgary Real Estate News under the heading Condo Corner. Although the information below is applicable in many jurisdictions, it is based on the law in Alberta, Canada, and is written from a practical real estate perspective in the City of Calgary.
			</p> 
			<br/> 
			<h2>Latest Articles</h2>
			%block_newest_articles%
			<h2>Most Popular Articles</h2>
			%block_popular_articles%
			<br/><br/> 
			</td> 
			</tr> 
			</table>").")");
		$articles_pageid = $this->db->insert_id();
		
		$this->db->query("INSERT INTO pages (page_templateid, pageof, menu, title, datemodified, published, position, urlname, content) VALUES (".
			"'3',".
			"'12',".
			"'main',".
			"'New Condos',".
			"NOW(),".
			"'1',".
			"'1',".
			"'new-condos',".
			$this->db->escape("<h2>Consult me before you shop for a new condo home!</h2> 
 
			<p>With the real estate market now turning to the buyers' favour, several new-home condo builders are willing to pay me from their marketing budgets to help my clients buy new homes. This means that at no cost to you I can help you choose your building and your suite, negotiate possible upgrades or additions, review the condo documents with you prior to proceeding, ensure that your possession is smooth and that you're welcomed with a proper housewarming gift.</p> 
 
			<p>To help you start, <a href=\"/site/view/2\">read this article</a>, which deals with new-home condo buying and your resale market alternatives. Then give me a call <i>before</i> you drop by show suites. Let's talk about your criteria for a condominium home, discuss your financing and spending limit, your location preferences, and see which new or resale homes will meet your needs at the best-possible value.</p> 
 
			<p>Remember that after your purchase I take a \"client for life\" approach, so you'll have ongoing access to me for any assistance I can provide to you or your building, if you join its Board.</p> 
 
			<p style=\"text-align: right\">-Gerald</p>").")");
		
		$this->db->query("INSERT INTO pages (page_templateid, pageof, menu, title, datemodified, published, position, urlname, post_templateid, pagepostname, content) VALUES (".
			"'3',".
			"'12',".
			"'main',".
			"'Resale Condo Listings',".
			"NOW(),".
			"'1',".
			"'2',".
			"'resale-listings',".
			"'5',".
			"'1',".
			$this->db->escape("<h2>Inner-city condo home listings</h2> 
 
			<p> 
				These five buttons will present to you all the MLS-listed condo properties in grouped MLS neighbourhoods in descending price order, so just skip down to your price range.  There can be a lot of data in the results, so give it all a minute to download.<br/> 
				If you want property details, real estate rules require that you fill in a form with your name and contact details, but I do not contact people searching via my web site; it's only to satisfy real estate Board rules.  Anyway, there comes a point when you have to actually visit properties to choose a condo home, so give me a call and let's go see the real thing! - Gerald at 403-703-0675
			</p> 
 
			<p> 
				<img src=\"/includes/images/bullet.gif\" alt=\"\" /> <a href=\"http://condosincalgary.redmantech.ca/view_saved_search.php?mlss_rid=220\">North side of Bow River</a><br/><span class=\"small\">including Bridgeland, Renfrew, Sunnyside, Crescent Heights, Hillhurst, West Hillhurst and Briar Hill.</span><br/> 
				<img src=\"/includes/images/bullet.gif\" alt=\"\" /> <a href=\"http://condosincalgary.redmantech.ca/view_saved_search.php?mlss_rid=221\">Downtown</a><br/><span class=\"small\">including Downtown, Downtown West End (Mewata), Eau Claire and The Rivers (East Village).</span><br/> 
				<img src=\"/includes/images/bullet.gif\" alt=\"\" /> <a href=\"http://condosincalgary.redmantech.ca/view_saved_search.php?mlss_rid=222\">Beltline</a><br/><span class=\"small\">including Connaught, Victoria Crossing (Victoria Park), Mount Royal, Lower Mount Royal and Sunalta.</span><br/> 
				<img src=\"/includes/images/bullet.gif\" alt=\"\" /> <a href=\"http://condosincalgary.redmantech.ca/view_saved_search.php?mlss_rid=223\">Elbow River</a><br/><span class=\"small\">including Mission, Cliff Bungalow, Erlton, Rideau and Parkhill.</span><br/> 
				<img src=\"/includes/images/bullet.gif\" alt=\"\" /> <a href=\"http://condosincalgary.redmantech.ca/view_saved_search.php?mlss_rid=224\">South West</a><br/><span class=\"small\">including Bankview, South Calgary, Marda Loop and the Mount Royal University area (Richmond, Killarney, Rutland Park, CFB Currie, Lincoln Park, Garrison Green, Altador and Lakeview).</span><br/> 
			</p> 
 
			<hr/> 
 
			<h2>Featured resale listings</h2>
			%block_gerald-listings%
			%block_other-listings%").")");
		$resale_listings_pageid = $this->db->insert_id();
		
		$this->db->query("INSERT INTO pages (page_templateid, pageof, menu, title, datemodified, published, position, urlname, content) VALUES (".
			"'3',".
			"'12',".
			"'main',".
			"'Condo Directory',".
			"NOW(),".
			"'1',".
			"'3',".
			"'condos',".
			$this->db->escape("<h2>Notes</h2> 
			<ul> 
				<li>My web site hosts condominium documents at no charge. Why let your management firm charge you for your own documents? Be in touch to take advantage of this free service.</li> 
				<li>Where the condominium corporation has a web site that I'm aware of, I'll provide that address.</li> 
				<li>All contents of my web site are copyrighted, so may not be reproduced without written permission.</li> 
				<li>Corrections and updates are welcome.</li> 
				<li>Use this <a href=\"/file/page/condos/mlsmap.pdf\">MLS districts map</a> to become familiar with Calgary's neighbourhood names and locations.</li> 
			</ul> 
			<br/> 
			<hr/> 
				<div class=\"feature\"> 
					<div> 
						<h3>Search</h3> 
						
						<form action=\"/search\" method=\"post\">			
							%block_form%
							<input name=\"searchtext\" type=\"text\" class=\"text\" /> 
							<input type=\"submit\" value=\"Go\" name=\"search\" class=\"button\"/> 
						</form> 
					</div> 
				</div> 
			
			<h2>Condo buildings by neighbourhood</h2><p>%block_inner_communities%</p><h2>Areas outside Calgary's inner city</h2><p>%block_outer_communities%</p>").")");
		$directory_pageid = $this->db->insert_id();
		
		$this->db->query("INSERT INTO categories (name, urlname, heldback) VALUES (".$this->db->escape('Listing by Gerald').",".$this->db->escape('gerald').", 0)");
		$gerald_resale_category = $this->db->insert_id();
		
		$this->db->query("INSERT INTO categories (name, urlname, heldback) VALUES (".$this->db->escape('Listing by other brokerage').",".$this->db->escape('other').", 0)");
		$other_resale_category = $this->db->insert_id();
		
		$this->db->query("INSERT INTO pages (page_templateid, menu, title, datemodified, published, urlname) VALUES (".
			"'7',".
			"'nomenu',".
			"'Outer Communities',".
			"NOW(),".
			"'1',".
			"'outer-communities')");
		$outer_communities_pageid = $this->db->insert_id();
		
		// Populate site data from old dmcb cms 1 data		
	
		$categories = $this->db->query("SELECT * FROM category");
		$beltlineid = NULL;
		foreach ($categories->result_array() as $category)
		{
			$category['menu'] = "main";
			if ($category['categoryof'] != NULL)
			{
				$category['categoryof'] = $beltlineid;
			}
			else if (substr($category['title'], 0, 1) == "|")
			{
				$category['menu'] = "nomenu";
				$category['title'] = substr($category['title'], 1);
				$category['categoryof'] = $outer_communities_pageid;
			}
			else if ($category['type'] == "directory")
			{
				$category['categoryof'] = $directory_pageid;
			}
			else
			{
				$category['categoryof'] = $articles_pageid;
			}
			
			/* old way of doing articles as categories, not happening 
			if ($category['type'] == "article")
			{
				$this->db->query("INSERT INTO categories (name, urlname, heldback) VALUES (".$this->db->escape($category['title']).",".$this->db->escape(strtolower(preg_replace("/[,' ]+/", "_", $category['title']))).", 0)");
				
				$article_categories[$category['categoryid']] = $this->db->insert_id();
			} 
			else if ($category['type'] == "directory") */
			
			$this->db->query("INSERT INTO pages (menu, title, pageof, datemodified, published, position, urlname) VALUES (".
				$this->db->escape($category['menu']).",".
				$this->db->escape($category['title']).",".
				$this->db->escape($category['categoryof']).",".
				"NOW(),".
				"'1',".
				$this->db->escape($category['rank']).",".
				$this->db->escape(strtolower(strtolower(preg_replace('/[^a-z0-9-_]+/i',"", preg_replace("/\s/","-",$category['title']))))).")");
				
			$directory_pages[$category['categoryid']] = $this->db->insert_id();
			
			if ($category['title'] == "Beltline")
			{
				$beltlineid = $this->db->insert_id();
			}
		}
		$pages = $this->db->query("SELECT pageid FROM pages WHERE pageof = ".$this->db->escape($directory_pageid)." ORDER BY title ASC");
		$i=0;
		foreach ($pages->result_array() as $page)
		{
			$i++;
			$this->db->query("UPDATE pages SET position = '".$i."' WHERE pageid = ".$this->db->escape($page['pageid']));

		}
		$pages = $this->db->query("SELECT pageid FROM pages WHERE pageof = ".$this->db->escape($outer_communities_pageid)." ORDER BY title ASC");
		$i=0;
		foreach ($pages->result_array() as $page)
		{
			$i++;
			$this->db->query("UPDATE pages SET position = '".$i."' WHERE pageid = ".$this->db->escape($page['pageid']));

		}
		$this->db->query("DROP TABLE `category`");
		
		$users = $this->db->query("SELECT * FROM user");
		foreach ($users->result_array() as $user)
		{	
			$displayname = $user['displayname'];
			if (strpos($user['displayname'], "@") !== FALSE)
			{
				$displayname = substr($user['displayname'], 0, strpos($user['displayname'], "@"));
			}
			$displayname = str_replace("_"," ",str_replace("."," ",$displayname));
			
			if ($user['email'] == "dmcb@shaw.ca")
			{
				$this->db->query("INSERT INTO acls (userid, roleid, controller) VALUES ('".$user['userid']."','1','site')");
				$this->db->query("INSERT INTO users (userid, email, password, displayname, urlname, registered, datemodified, mailinglist, getmessages) VALUES (".
					$this->db->escape($user['userid']).",".
					"'derek@dmcbdesign.com',".
					$this->db->escape($user['password']).",".
					$this->db->escape($displayname).",".
					$this->db->escape(strtolower(str_replace(" ","-",$displayname))).",".
					$this->db->escape($user['registered']).",".
					$this->db->escape($user['registered']).",".
					"'1',".
					"'1')");	
			}
			else
			{
				if ($user['type'] == "admin")
				{
					$this->db->query("INSERT INTO acls (userid, roleid, controller) VALUES ('".$user['userid']."','2','site')");
				}
				else
				{
					$this->db->query("INSERT INTO acls (userid, roleid, controller) VALUES ('".$user['userid']."','4','site')");
				}
				$this->db->query("INSERT INTO users (userid, email, password, displayname, urlname, registered, datemodified, mailinglist, getmessages) VALUES (".
					$this->db->escape($user['userid']).",".
					$this->db->escape($user['email']).",".
					$this->db->escape($user['password']).",".
					$this->db->escape($displayname).",".
					$this->db->escape(str_replace(" ","-",$displayname)).",".
					$this->db->escape($user['registered']).",".
					$this->db->escape($user['registered']).",".
					"'1',".
					"'1')");	
			}
			
			$existing_users[$user['userid']] = $this->db->insert_id();
		}
		$this->db->query("DROP TABLE `user`");
		
		$pages = $this->db->query("SELECT * FROM page");
		foreach ($pages->result_array() as $page)
		{	
			$url_prefix = "";
			if ($page['categoryid'] == 0)
			{
				$pageid = $resale_listings_pageid;
				$url_prefix = "resale-listings/post/";
			}
			else if ($page['categoryid'] <= 11 || $page['categoryid'] == 43)
			{
				$parent_page_query = $this->db->query("SELECT urlname FROM pages WHERE pageid = ".$this->db->escape($directory_pages[$page['categoryid']]));
				$parent_page = $parent_page_query->row_array();
				$url_prefix = $parent_page['urlname']."/post/";
				$pageid = $directory_pages[$page['categoryid']];
				/*
				$url_prefix = "articles/post/";
				$pageid = $articles_pageid;
				*/
			}
			else
			{
				$parent_page_query = $this->db->query("SELECT urlname FROM pages WHERE pageid = ".$this->db->escape($directory_pages[$page['categoryid']]));
				$parent_page = $parent_page_query->row_array();
				$url_prefix = $parent_page['urlname']."/post/";
				$pageid = $directory_pages[$page['categoryid']];
			}
		
			if (!isset($page['views']))
			{
				$page['views'] = 0;
			}
			
			$this->db->query("INSERT INTO posts (pageid, views, title, content, date, datemodified, published, urlname) VALUES (".
				$this->db->escape($pageid).",".
				$this->db->escape($page['views']).",".
				$this->db->escape($page['title']).",".
				$this->db->escape($page['content']).",".
				$this->db->escape($page['date']).",".
				$this->db->escape($page['date']).",".
				"'1',".
				$this->db->escape($url_prefix.strtolower(strtolower(preg_replace('/[^a-z0-9-_]+/i',"", preg_replace("/\s/","-",$page['title']))))).")");
			
			$postid = $this->db->insert_id();			
				
			$this->db->query("INSERT INTO placeholders (attachedto, oldname, newname, date, redirect) VALUES (".
				"'page',".
				$this->db->escape('site/view/'.$page['pageid']).",".
				$this->db->escape($url_prefix.strtolower(strtolower(preg_replace('/[^a-z0-9-_]+/i',"", preg_replace("/\s/","-",$page['title']))))).",".
				$this->db->escape($page['date']).",".
				"'1')");
				
			/* No longer 
			if (($page['categoryid'] != 0 && $page['categoryid'] <= 11) || $page['categoryid'] == 43)
			{
				$this->db->query("INSERT INTO posts_categories (categoryid, postid) VALUES (".
				$this->db->escape($article_categories[$page['categoryid']]).",".
				$this->db->insert_id().")");
			} */
			
			if (isset($page['mylisting']))
			{
				if ($page['mylisting'] == "1")
				{
					$this->db->query("INSERT INTO posts_categories (categoryid, postid) VALUES (".
					$this->db->escape($gerald_resale_category).",".
					$this->db->escape($postid).")");
				}
				else
				{
					$this->db->query("INSERT INTO posts_categories (categoryid, postid) VALUES (".
					$this->db->escape($other_resale_category).",".
					$this->db->escape($postid).")");
				}
			}
			else if ($page['categoryid'] > 11 && $page['categoryid'] != 43 && $page['pageid'] == $outer_communities_pageid)
			{
				$this->db->query("INSERT INTO templates_fields_values (templateid, htmlcode, attachedto, attachedid, value) VALUES ".
				"('8', 'location', 'post', ".$this->db->escape($postid).", ".$this->db->escape($page['subject']).")");			
			}
			else if ($page['categoryid'] > 11 && $page['categoryid'] != 43)
			{
				$this->db->query("INSERT INTO templates_fields_values (templateid, htmlcode, attachedto, attachedid, value) VALUES ".
				"('6', 'location', 'post', ".$this->db->escape($postid).", ".$this->db->escape($page['subject']).")");
			}
			
			$directory_posts[$page['pageid']] = $postid;
		}
		$pages = $this->db->query("SELECT * FROM page WHERE categoryid = '0'");
		foreach ($pages->result_array() as $page)
		{		
			$postid = $directory_posts[$page['pageid']];
			$this->db->query("INSERT INTO templates_fields_values (templateid, htmlcode, attachedto, attachedid, value) VALUES ".
			"('5', 'location', 'post', ".$this->db->escape($postid).", ".$this->db->escape($page['subject'])."),".
			"('5', 'statistics', 'post', ".$this->db->escape($postid).", ".$this->db->escape($page['stats'])."),".
			"('5', 'mls', 'post', ".$this->db->escape($postid).", ".$this->db->escape($page['mls'])."),".
			"('5', 'price', 'post', ".$this->db->escape($postid).", ".$this->db->escape($page['price']).")");
			
			$url_query = $this->db->query("SELECT urlname FROM posts WHERE postid = ".$this->db->escape($directory_posts[$page['linkid']]));
			$url = $url_query->row_array();
			if (isset($url['urlname']))
			{
				$this->db->query("INSERT INTO templates_fields_values (templateid, htmlcode, attachedto, attachedid, value) VALUES ('5', 'url', 'post', ".$this->db->escape($postid).", ".$this->db->escape($url['urlname']).")");
			}
		}
		$this->db->query("DROP TABLE `page`");
		
		$contributors = $this->db->query("SELECT * FROM condo_contributors");
		foreach ($contributors->result_array() as $contributor)
		{	
			$this->db->query("INSERT INTO acls (userid, roleid, controller, attachedid) VALUES (".
			$this->db->escape($existing_users[$contributor['userid']]).",".
			$this->db->escape($condo_doc_contributor_roleid).",".
			"'post',".
			$this->db->escape($directory_posts[$contributor['pageid']]).")");
		}
		$this->db->query("DROP TABLE `condo_contributors`");
		$this->db->query("DROP TABLE `condo_readers`");
		
		$images = $this->db->query("SELECT * FROM image");
		foreach ($images->result_array() as $image)
		{	
			$filename = substr($image['url'], (strrpos($image['url'], '/')+1));
			$this->db->query("INSERT INTO files (filename, extension, isimage, attachedto, attachedid) VALUES (".
			$this->db->escape($filename).",".
			"'jpg',".
			"'1',".
			"'post',".
			$this->db->escape($directory_posts[$image['pageid']]).")");		
			$this->db->query("UPDATE posts SET imageid = ".$this->db->insert_id()." WHERE postid = ".$this->db->escape($directory_posts[$image['pageid']]));
			
			$post_query = $this->db->query("SELECT urlname FROM posts WHERE postid = ".$this->db->escape($directory_posts[$image['pageid']]));
			$post = $post_query->row_array();
			$newpath = "files/post/".str_replace("/", "+", $post['urlname'])."/";
			$oldpath = substr($image['url'], 1).".jpg";
			
			if (!file_exists($newpath))
			{			
				mkdir($newpath);
			}
			copy($oldpath, $newpath.$filename.".jpg");
		}
		$this->db->query("DROP TABLE `image`");
		
		$documents = $this->db->query("SELECT * FROM documents");
		foreach ($documents->result_array() as $document)
		{	
			if (file_exists("condodocs/".$document['pageid'].'/'.$document['filename']))
			{
				$filename = substr($document['filename'], 0, strrpos($document['filename'], '.'));
				$filename = preg_replace('/[^a-z0-9-_]+/i',"", $filename);
				$extension = substr($document['filename'], (strrpos($document['filename'], '.')+1));
				
				if ($filename && $extension)
				{
					$header_query = $this->db->query("SELECT title FROM document_headers WHERE headerid = ".$this->db->escape($document['headerid']));
					$header = $header_query->row_array();
					$oldheader = ucfirst(strtolower($header['title']));
					
					$filetype_query = $this->db->query("SELECT filetypeid FROM quotas_filetypes WHERE name = ".$this->db->escape($oldheader));
					$filetype = $filetype_query->row_array();
					$filetypeid = $filetype['filetypeid'];			
					
					$this->db->query("INSERT INTO files (filename, extension, isimage, attachedto, attachedid, downloadcount, filetypeid) VALUES (".
					$this->db->escape($filename).",".
					$this->db->escape($extension).",".
					"'0',".
					"'post',".
					$this->db->escape($directory_posts[$document['pageid']]).",".
					$this->db->escape($document['downloadcount']).",".
					$filetypeid.")");	
					
					$post_query = $this->db->query("SELECT urlname FROM posts WHERE postid = ".$this->db->escape($directory_posts[$document['pageid']]));
					$post = $post_query->row_array();
					$newpath = "files/post/".str_replace("/", "+", $post['urlname'])."/";
					
					if (!file_exists($newpath))
					{			
						mkdir($newpath);
					}
					copy("condodocs/".$document['pageid'].'/'.$document['filename'], $newpath.$filename.'.'.$extension);
				}
			}
		}
		$this->db->query("DROP TABLE `documents`");
		$this->db->query("DROP TABLE `document_headers`");
	}
	
	/* Patches from dmcb cms 2 to dmcb cms 4 */
	
	function patch_to_acl_users()
	{
		$this->load->model(array('acls_model','users_model'));
		$users = $this->users_model->get_all();
		$roleid = $this->acls_model->get_roleid("member");
		foreach ($users->result_array() as $user)
		{
			$acl = $this->acls_model->get($user['userid'], 'site');
			if ($acl == NULL)
			{
				$this->acls_model->add($user['userid'], $roleid, 'site');
			}
		}
	}
	
	function patch_to_update_post_urlnames()
	{
		$posts = $this->db->query("SELECT * FROM posts");
		foreach ($posts->result_array() as $post)
		{
			$urlname = date("Ymd", strtotime($post['date'])).'/'.$post['urlname'];
			$this->db->query("UPDATE posts SET urlname = '".$urlname."' WHERE postid='".$post['postid']."'");
			$content = str_replace("/file/post/".$post['urlname'], "/file/post/".$urlname, $post['content']);
			$this->db->query("UPDATE posts SET content = ".$this->db->escape($content)." WHERE postid='".$post['postid']."'");
		}
	}	
	
	function patch_to_managed_files()
	{
		$data['subject'] = "Log";
		$data['message'] = "Patch log:<br/><br/>";
		
		$makedirectories = array("files_managed", "files_managed/user", "files_managed/post", "files_managed/page", "files_managed/site");
		foreach ($makedirectories as $makedirectory)
		{
			if (!file_exists($makedirectory))
			{			
				mkdir($makedirectory);
			}
		}
		$directories = array("post", "page", "user");
		foreach ($directories as $directory)
		{
			$path = "files/".$directory;
			if ($handle = opendir($path))
			{
				$data['message'] .= "Dealing with ".$directory."s<br/>";
				while (false !== ($dirfile = readdir($handle))) 
				{
					if ($dirfile != "." && $dirfile != "..")
					{
						$data['message'] .= "Looking at ".$dirfile."<br/>";
						if (is_numeric($dirfile))
						{
							$attachedid = NULL;
							$data['message'] .= $dirfile." is going to be migrated<br/>";
							if ($directory == "post")
							{
								$post = instantiate_library('post', $dirfile);
								$attachedid = $post->post['postid'];
								$newpath = $post->post['urlname'];
								$content = $post->post['content'];
								//stick in width/height parameter regex
								$content = preg_replace("/\/file\/post\/".$newpath."\/(\d+)\/(\d+)\/(\w+\.\w+)\"/", "/file/post/".$newpath."/$3/$1/$2\"", $content);
								$content = preg_replace("/\/file\/post\/".$newpath."\/(\d+)\/(\w+\.\w+)\"/", "/file/post/".$newpath."/$2/$1\"", $content);
								$this->db->query("UPDATE posts SET content = ".$this->db->escape($content)." WHERE postid=".$this->db->escape($dirfile));
							}
							else if ($directory == "page")
							{
								$page = instantiate_library('page', $dirfile);
								$attachedid = $page->page['pageid'];
								$newpath = $page->page['urlname'];
								$content = $page->page['content'];
								//stick in width/height parameter regex
								$content = preg_replace("/\/file\/page\/".$newpath."\/(\d+)\/(\d+)\/(\w+\.\w+)\"/", "/file/page/".$newpath."/$3/$1/$2\"", $content);
								$content = preg_replace("/\/file\/page\/".$newpath."\/(\d+)\/(\w+\.\w+)\"/", "/file/page/".$newpath."/$2/$1\"", $content);
								$this->db->query("UPDATE pages SET content = ".$this->db->escape($content)." WHERE pageid=".$this->db->escape($dirfile));

							}
							else if ($directory == "user")
							{
								$user = instantiate_library('user', $dirfile);
								$attachedid = $user->user['userid'];
								$newpath = $user->user['urlname'];
							}
							$newpath = str_replace("/", "+", $newpath);
							$data['message'] .= $dirfile." will become ".$newpath."<br/>";
							rename($path."/".$dirfile, $path."/".$newpath);
							
							$filepieces = explode(".", $dirfile);
							$extension = $filepieces[count($filepieces)-1];
							$filename = substr($dirfile, 0, strrpos($dirfile, "."));
							
							$file = instantiate_library('file', array($filename, $extension, $directory, $attachedid), 'details');
							$file->manage();
						}
					}
				}
				closedir($handle);
			}
		}
		$this->_message("Patch", $data['message'], $data['subject']);
	}
	
	function patch_default_templates()
	{
		$this->db->query("ALTER TABLE  `templates` CHANGE  `attachedid`  `pageid` INT( 10 ) UNSIGNED NULL DEFAULT NULL;");
		$this->db->query("ALTER TABLE  `templates` DROP  `attachedto`;");
		$this->db->query("ALTER TABLE  `pages` ADD  `rss_blockid` INT UNSIGNED NULL , ADD  `pagination_blockid` INT UNSIGNED NULL;");
		$this->db->query("CREATE TABLE  `templates_defaults` (
`templateid` INT( 10 ) UNSIGNED NOT NULL ,
`pageid` INT( 10 ) UNSIGNED NOT NULL ,
PRIMARY KEY (  `templateid` ,  `pageid` )
) ENGINE = MYISAM ;");	
		$this->db->query("CREATE TABLE  `blocks_defaults` (
`blockinstanceid` INT( 10 ) UNSIGNED NOT NULL ,
`pageid` INT( 10 ) UNSIGNED NOT NULL ,
`type` VARCHAR( 10 ) NOT NULL ,
PRIMARY KEY (  `pageid` ,  `type` )
) ENGINE = MYISAM ;");			
		
		$this->db->query("UPDATE templates SET pageid = 0 WHERE pageid IS NULL");
	
		$default_blocks = $this->db->query("SELECT * FROM blocks_instances WHERE rss = '1' OR pagination = '1'");
		foreach ($default_blocks->result_array() as $default_block)
		{	
			if ($default_block['rss'] == 1)
			{
				$this->db->query("INSERT INTO blocks_defaults (blockinstanceid, pageid, type) VALUES (".
				$this->db->escape($default_block['blockinstanceid']).",".
				$this->db->escape($default_block['pageid']).",".
				$this->db->escape("rss").")");
				
				$this->db->query("UPDATE pages SET rss_blockid = ".$this->db->escape($default_block['blockinstanceid']).
					" WHERE pageid = ".$this->db->escape($default_block['pageid']));
			}
			if ($default_block['pagination'] == 1)
			{
				$this->db->query("INSERT INTO blocks_defaults (blockinstanceid, pageid, type) VALUES (".
				$this->db->escape($default_block['blockinstanceid']).",".
				$this->db->escape($default_block['pageid']).",".
				$this->db->escape("pagination").")");
				
				$this->db->query("UPDATE pages SET pagination_blockid = ".$this->db->escape($default_block['blockinstanceid']).
					" WHERE pageid = ".$this->db->escape($default_block['pageid']));
			}
		}
		
		$this->db->query("ALTER TABLE  `blocks_instances` DROP  `rss`;");
		$this->db->query("ALTER TABLE  `blocks_instances` DROP  `pagination`;");
		
		$default_templates = $this->db->query("SELECT * FROM templates WHERE standard = '1'");
		foreach ($default_templates->result_array() as $default_template)
		{	
			$this->db->query("INSERT INTO templates_defaults (templateid, pageid) VALUES (".
			$this->db->escape($default_template['templateid']).",".
			$this->db->escape($default_template['pageid']).")");
		}
		
		$this->db->query("ALTER TABLE  `templates` DROP  `standard`;");
	}
}

?>