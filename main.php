
		<main>
			<h1>Analytics. <span>Rijeka in Flux</span> mobile app</h1>
			<section>

				<p class="toggle-btns" style="text-align: right;">
					<button class="region active" onclick="showMap('region');">Europe</button>
					<button class="world" onclick="showMap('world');">World</button>
					<button title="shows data clustered around cities" class="region-cluster" onclick="showMap('region-cluster');">Croatia*</button>
					<button title="data data clustered around cities" class="na-cluster" onclick="showMap('na-cluster');">North America*</button>

				</p>

				<div id="regions_div" style="" class="metric-section region"></div>
				<div id="regions_div_" class="hidden metric-section world" style=""></div>
				<div id="regions_cluster_div" class="hidden metric-section region-cluster" style=""></div>
				<div id="regions_cluster_div_" class="hidden metric-section na-cluster" style=""></div>

			<section>

			<section>

				<div id="donuts_" class="metric-section donuts">
					<div id="donut_local" class=""></div>
					<div id="donut_local_views" class=""></div>
				</div>


				<p  class="toggle-btns" style="text-align: right;"><button class="all-metrics active" onclick="showMainMetric('all');">All</button><button class="croatia-metrics" onclick="showMainMetric('croatia');">Croatia</button><button class="other-metrics" onclick="showMainMetric('other');">Other</button></p>


				<div id="metrics_div" class="metric-section metrics-group all-metrics">
					<div id="metric_total"></div>
					<div id="metric_ips"></div>
					<div id="metric_today"></div>
					<div id="metric_7days"></div>
					<div id="metric_month"></div>
					<div id="metric_lastMonth"></div>
				</div>

				<div id="metrics_div_croatia" class="hidden metric-section metrics-group croatia-metrics">
					<div id="metric_total_croatia"></div>
					<div id="metric_ips_croatia"></div>
					<div id="metric_today_croatia"></div>
					<div id="metric_7days_croatia"></div>
					<div id="metric_month_croatia"></div>
					<div id="metric_lastMonth_croatia"></div>
				</div>

				<div id="metrics_div_other" class="hidden metric-section metrics-group other-metrics">
					<div id="metric_total_other"></div>
					<div id="metric_ips_other"></div>
					<div id="metric_today_other"></div>
					<div id="metric_7days_other"></div>
					<div id="metric_month_other"></div>
					<div id="metric_lastMonth_other"></div>
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


				
				<div id="chart_12_months_region" class="metric-section">
				</div>
				


				<h2>Unique section views</h2>
				<p>*These are not the only sections that are logged as `section views`, but are easily distinguished from others. Because some section views do not request unique data, they cannot be identified as a specific app section and are not included below.</p>
				
				<p class="toggle-btns"  style="text-align: right;"><button class="tours active" onclick="showMetrics('tours');">Tours</button><button class="categories" onclick="showMetrics('categories');">Categories</button><button class="researchers" onclick="showMetrics('researchers');">Researchers</button></p>
				<p style="text-align: right;"><label class="unique-u">Unique users</label> <label class="total-u" >Total views</label></p>
				<div class="metric-section with-overlay tours">
				
					<div id="metrics_tours_div_unique" class=" metrics-group">	
					</div>

					<div id="metrics_tours_div" class=" metrics-group metrics-overlay">	
					</div>
				</div>


				<div class="metric-section with-overlay categories hidden">
				
					<div id="metrics_categories_div_unique" class=" metrics-group">	
					</div>

					<div id="metrics_categories_div" class=" metrics-group metrics-overlay">	
					</div>
				</div>

				<div class="metric-section with-overlay researchers hidden">
					
					<div id="metrics_researchers_div_unique" class=" metrics-group">	
					</div>

					<div id="metrics_researchers_div" class=" metrics-group metrics-overlay">	
					</div>
					<p>*Top 6 researchers only</p>
				</div>


				


				<div id="chart_distribution" class="metric-section">
				</div>


				<p>
					Activity distribution (histogram) shows the total number of unique users (unique IPs) grouped on the number of sections views that were logged for each user. 
					The shape of the chart is expected to follow a half-normal distribution (the right half of a normal distribution centered around 0). 
					This assumes a smaller group of highly active users and a larger group of casual viewers.
					<br/><br/>
					*Note that this graph is grouping the items along the horizontal (x-axis) using a log<sub>2</sub> algorithm, meaning the grouping sizes increase along the x-axis.
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


				<div id="chart_retention" class="metric-section">
				</div>
				
				<p>
					User retention chart shows the number of unique users (unique IPs) grouped on the number of days between their first and last recorded visits. 
					<br/><br/>
					*Note that this graph is grouping the items along the horizontal (x-axis) using a log<sub>2</sub> algorithm, meaning the grouping sizes increase along the x-axis.
				</p>

			<section>

		</main>