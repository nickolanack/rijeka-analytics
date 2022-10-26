
		<main>
			<h1>Analytics. <span>Rijeka in Flux</span> mobile app</h1>
			<section>
				<p style="text-align: right;"><button class="region active" onclick="showRegion();">Region</button><button class="world" onclick="showWorld();">World</button></p>
				<div id="regions_div" style="" class="metric-section">
					
				</div>
				<div id="regions_div_" class="hidden metric-section" style="">
					
				</div>



			<section>

			<section>

				<div id="donuts_" class="metric-section donuts">
					<div id="donut_local" class=""></div>
					<div id="donut_local_views" class=""></div>
				</div>


				<div id="metrics_div" class="metric-section">
					<div id="metric_total"></div>
					<div id="metric_ips"></div>
					<div id="metric_today"></div>
					<div id="metric_7days"></div>
					<div id="metric_month"></div>
					<div id="metric_lastMonth"></div>
				</div>

				<p>
					The app requests section data each time a user navigates to a new section within the app (ie: each curated tour is a section), these 
					are counted as section views.
					<br/><br/>
					The app does not track users and does not collect or store any identifying information about individual users. Therefore, the only data (collected by us) that can be 
					used to indicate a unique user is the IP address; often a device uses the same IP address for a long period of time, unless they switch between networks,
					so that the IP address is an approximate indicator of unique users. Over a long time period, the unique IPs are probably an overestimate of unique users but 
					it is likely to be more accurate over short periods.
					<br/><br/>
					Daily, weekly, and monthly metrics are calculated using unique IPs unless labeled otherwise. Dates and times are formatted for Europe/Zagreb timezone.


				</p>


				<div id="chart_12_months" class="metric-section">
				</div>

				


				<h2>Unique section views</h2>
				<p>*These are not the only sections that are logged as `section views`, but are easily distinguished from others. Because some section views do not request unique data, they cannot be identified as a specific app section and are not included below.</p>
				
				<p style="text-align: right;"><label class="unique-u">Unique users</label> <label class="total-u" >Total views</label></p>
				<div class="metric-section with-overlay">
				
					<div id="metrics_tours_div_unique" class=" metrics-group">	
					</div>

					<div id="metrics_tours_div" class=" metrics-group metrics-overlay">	
					</div>
				</div>

				


				<div id="chart_distribution" class="metric-section">
				</div>


				<p>
					Activity distribution (histogram) shows the total number of unique users (unique ips) grouped on the number of sections views that were logged for each user. 
					The shape of the chart is expected to follow a half-normal distribution (the right half of a normal distribution centered around 0). 
					This assumes a smaller group of highly active users and a larger group of casual viewers.
				</p>
				<p>
					For the following metrics unique user data is seperated into two groups; casual < 16, and active >= 16.


				</p>
				
				<h2>Active user section views compared with casual users</h2>
				<p style="text-align: right;"><label class="active-u">Active users</label> <label class="casual-u" >Casual users</label></p>
				<div id="metrics_tours_div_active"  class="metric-section">
					<div id="metrics_tours_div_active_items">

					</div>

					<div id="metrics_tours_div_casual">
					</div>
				</div>



				<div id="chart_12_months_active" class="metric-section">
				</div>

				<div id="donuts" class="metric-section">
				
					<div id="donut_active">
					</div>

					<div id="donut_active_last1Months">
					</div>
				</div>
				

			<section>

		</main>