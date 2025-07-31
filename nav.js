// Function to create header elements including hamburger menu, user profile image, and logo
function createHeader() {
	const headerContainer = document.createElement('div');
	headerContainer.className = 'header-container';
	headerContainer.style.position = 'fixed';
	headerContainer.style.top = '0';
	headerContainer.style.left = '0';
	headerContainer.style.width = '100%';
	headerContainer.style.height = '40px';
	headerContainer.style.display = 'flex';
	headerContainer.style.justifyContent = 'space-between';
	headerContainer.style.alignItems = 'center';
	headerContainer.style.zIndex = '1000';
	headerContainer.style.backgroundColor = 'lightgrey';

	// Create hamburger menu icon
	const icon = document.createElement('div');
	// icon.appendChild(profileImage);
	icon.textContent = '☰';
	icon.style.fontSize = '30px';
	icon.style.padding = '3px 3px';
	icon.style.cursor = 'pointer';
	icon.style.color = 'var(--primary)';
	icon.style.position = 'relative'; // For proper positioning with profile image overlay
	icon.style.marginRight = '17px';
	icon.addEventListener('click', toggleNav);

	// Create profile image only if ContactID exists
	const contactID = localStorage.getItem('ContactID');
	if (contactID) {
		const profileImage = document.createElement('img');
		profileImage.alt = 'User Profile';
		profileImage.style.position = 'absolute';
		profileImage.style.top = '50%';
		profileImage.style.right = '-45px'; // Overlay on the hamburger menu
		profileImage.style.transform = 'translateY(-50%)';
		profileImage.style.width = '36px';
		profileImage.style.height = '36px';
		profileImage.style.borderRadius = '50%';
		profileImage.style.zIndex = '1001'; // Ensure it overlays the hamburger menu
		profileImage.style.cursor = 'pointer';

		// Set the default profile image
		profileImage.src = '/images/default_profile.png';

		// Attempt to load the user-specific profile image
		const tempImage = new Image(); // Use a temporary image object to validate the new image
		tempImage.onload = function () {
			// Replace the default image with the loaded profile image
			profileImage.src = `/serve_file.php?file=${encodeURIComponent(contactID)}.webp`;
		};
		tempImage.onerror = function () {
			// console.log("User-specific profile image not found, using default.");
		};
		tempImage.src = `/serve_file.php?file=${encodeURIComponent(contactID)}.webp`;

		// Append the profile image to the desired parent element
		icon.appendChild(profileImage);
	} else {
		console.log("No ContactID found, profile image will not be displayed.");
	}

	// Assuming this part is within a function or similar structure where it fits into your overall logic
	const logoLink = document.createElement('a');
	// logoLink.href = "/ua1_auth.html?mode=logout";
	logoLink.href = "/splashpage.html";
	logoLink.id = "logo-link";
	const logoImage = document.createElement('img');
	logoImage.src = contactID ? `/logo.php?contactID=${encodeURIComponent(contactID)}` : '/images/default_logo.png';
	logoImage.alt = "Logo";
	logoImage.style.height = '30px';
	logoImage.style.width = 'auto';
	logoLink.appendChild(logoImage);

	// Append hamburger icon and logo to the header container
	headerContainer.appendChild(icon);
	headerContainer.appendChild(logoLink);
	document.body.insertBefore(headerContainer, document.body.firstChild);

	// Create navigation element
	const nav = document.createElement('nav');
	nav.style.position = 'fixed';
	nav.style.top = '40px';
	nav.style.width = '0';
	nav.style.height = '100%';
	nav.style.backgroundColor = '#333';
	nav.style.color = 'white';
	nav.style.zIndex = '999';
	nav.style.display = 'none';
	nav.style.transition = 'width 0.3s ease';

	const ul = document.createElement('ul');
	ul.id = 'nav-list';
	ul.style.listStyleType = 'none';
	ul.style.padding = 0;
	ul.style.margin = 0;

	nav.appendChild(ul);
	document.body.insertBefore(nav, headerContainer.nextSibling);
}

//====================================================
//================== THEME  & LOGO ===================
//====================================================
function applyThemeColorsFromLocalStorage() {
	const themeColors = localStorage.getItem('themeColors');
	if (themeColors) {
		applyThemeColors(JSON.parse(themeColors));
	} else {
		fetchThemeColors();
	}
}

function applyThemeColors(colors) {
	const root = document.documentElement;
	Object.keys(colors).forEach(key => {
		root.style.setProperty(key, colors[key]);
	});
}

function fetchThemeColors() {
	const contactID = localStorage.getItem('ContactID');
	fetch(`theme.php?contactID=${contactID}`)
		.then(response => response.json())
		.then(data => {
			localStorage.setItem('themeColors', JSON.stringify(data.colors));
			applyThemeColors(data.colors);
		})
		.catch(error => console.error('Failed to fetch theme colors:', error));
}
//====================================================

// Event to toggle navigation visibility
function toggleNav(event) {
	const nav = document.querySelector('nav');
	nav.style.display = nav.style.display === 'block' ? 'none' : 'block';
	nav.style.width = nav.style.width === '45%' ? '0' : '45%';
	if (event) {
		event.stopPropagation();
	}
}

// Generate navigation items based on navLinks array
function generateNav(navLinks) {
	if (!Array.isArray(navLinks)) {
		console.error("navLinks is not an array:", navLinks);
		return;
	}

	const navList = document.getElementById('nav-list');
	const currentPage = window.location.pathname.toLowerCase();

	const generateNavItem = (item) => {
		if (!item || !item.href || !item.title) {
			console.error("Invalid nav item:", item);
			return null;
		}

		if (item.Hidden === 'Y') {
			console.error("Hidden nav item:", item);
			return null;
		}

		const li = document.createElement('li');
		li.style.padding = '5px 20px';
		li.style.backgroundColor = 'var(--secondary-light)';
		li.style.borderBottom = '1px solid #ddd';

		const a = document.createElement('a');
		a.href = item.href;
		a.textContent = item.title;
		a.style.color = '#333';
		a.style.textDecoration = 'none';

		if (currentPage.endsWith(item.href.toLowerCase()) || currentPage.includes(item.href.toLowerCase())) {
			const indicator = document.createElement('span');
			indicator.textContent = '►';
			indicator.style.color = 'var(--primary)';
			indicator.style.paddingRight = '5px';
			a.prepend(indicator);
			a.style.fontWeight = 'bold';
		}

		li.appendChild(a);
		return li;
	};

	const sectionMap = new Map();
	const subSectionMap = new Map();

	navLinks.forEach(item => {
		if (!item.Section) {
			// If no Section is provided, treat it as a standalone navigation item
			const navItem = generateNavItem(item);
			if (navItem) {
				navList.appendChild(navItem);
			}
		} else {
			// Ensure Section exists
			if (!sectionMap.has(item.Section)) {
				const { li: sectionLi, subUl: sectionUl } = generateNavParentItem(item.Section);
				navList.appendChild(sectionLi);
				sectionMap.set(item.Section, sectionUl);
			}

			// Ensure SubSection exists within the Section
			if (!subSectionMap.has(item.SubSection)) {
				const { li: subSectionLi, subUl: subSectionUl } = generateNavParentItem(item.SubSection);
				sectionMap.get(item.Section).appendChild(subSectionLi);
				subSectionMap.set(item.SubSection, subSectionUl);
			}

			// Append the navigation item under the correct SubSection
			const navItem = generateNavItem(item);
			if (navItem) {
				subSectionMap.get(item.SubSection).appendChild(navItem);
			}
		}
	});
}

function generateNavParentItem(title) {
	const li = document.createElement('li');
	li.style.padding = '5px 20px';
	li.style.backgroundColor = 'var(--secondary)'; // Parent background color
	li.style.borderBottom = '1px solid #ddd';

	const span = document.createElement('span');
	span.textContent = title; // Correctly setting section/subsection name
	span.style.color = '#333';
	span.style.cursor = 'pointer';

	const subUl = document.createElement('ul');
	subUl.style.listStyleType = 'none';
	subUl.style.marginTop = '5px';
	subUl.style.display = 'none';
	subUl.style.backgroundColor = 'var(--secondary-light)'; // Children background color

	// Click event for expanding/collapsing sections
	span.addEventListener('click', function (e) {
		e.preventDefault();
		subUl.style.display = (subUl.style.display === 'none') ? 'block' : 'none';
	});

	li.appendChild(span);
	li.appendChild(subUl);

	return { li, subUl }; //Only one return statement
}

// List of pages that should load default navlinks without fetching from the server
const defaultPages = [
];

// Fetch navigation links from the server or load default if current page is in the defaultPages list
function fetchNavLinks() {
	const currentPage = window.location.pathname.toLowerCase();
	const isSplashPage = currentPage.endsWith("splashpage.html");

	// Check if the current page is in the list of default pages
	if (defaultPages.includes(currentPage)) {
		console.log('Loading default navlinks for', currentPage);
		loadDefaultNavLinks();
		return;
	}

	// If not splash page, check sessionStorage first
	if (!isSplashPage) {
		const sessionNav = sessionStorage.getItem('navLinks');
		if (sessionNav) {
			try {
				const navLinks = JSON.parse(sessionNav);
				console.log('Using sessionStorage navLinks');
				generateNav(navLinks);
				return;
			} catch (e) {
				console.error('Error parsing session navLinks:', e);
				sessionStorage.removeItem('navLinks');
			}
		}
	} else {
		console.log("splashpage.html detected – refreshing sessionStorage");
	}

	// Always fetch new links for splashpage.html or if no session data
	const contactID = localStorage.getItem('ContactID');
	const encrypKey = localStorage.getItem('EncrypKey');
	const requestBody = {
		ContactID: contactID,
		EncrypKey: encrypKey
	};

	postData('/nav_get.php', requestBody)
		.then(response => {
			if (response.success) {
				sessionStorage.setItem('navLinks', JSON.stringify(response.navLinks));
				// if (isSplashPage) {
					// console.log("Reloading splashpage.html after nav data is cached.");
					// location.reload();
					// return; // Stop further execution
				// }
				if (isSplashPage) {
					console.log("splashpage.html detected – refreshing sessionStorage");

					const alreadyReloaded = sessionStorage.getItem('navReloaded');
					if (!alreadyReloaded) {
						sessionStorage.setItem('navReloaded', 'true');
						location.reload();
						return;
					}
				}

				generateNav(response.navLinks);
			} else {
				console.error('Failed to load navigation links:', response.message);
				loadDefaultNavLinks();
			}
		})
		.catch(error => {
			console.error('Error fetching navigation links:', error);
			loadDefaultNavLinks();
		});
}

// Load default navigation links if fetching from the server fails or for specific pages
function loadDefaultNavLinks() {
	const defaultNavLinks = [
		// { href: "/index_portal.html", title: "Home" },
		// { href: "/solutions.html", title: "Solutions" },
		// { href: "/welcome.html", title: "Roles" }
		{ href: "/ua1_auth.html?mode=logout", title: "Logout" }
	];
	generateNav(defaultNavLinks);
}

document.addEventListener('DOMContentLoaded', () => {
	const suppressNavbar = document.body?.dataset?.noNavbar === "true";

	applyThemeColorsFromLocalStorage(); // always apply theme

	if (suppressNavbar || window.location.pathname.includes("_popup")) {
		return; // skip navbar build
	}

	createHeader();
	fetchNavLinks();
});

