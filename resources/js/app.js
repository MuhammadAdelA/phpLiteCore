// Import the main SCSS file.
// Webpack will see this, process it with sass-loader, css-loader,
// and MiniCssExtractPlugin will extract the result into app.css.
import '../scss/app.scss';

// Import Bootstrap's JavaScript (which might depend on Popper.js, already handled by Bootstrap's internals)
import 'bootstrap';

// Import SweetAlert2
import Swal from 'sweetalert2';

// Make Swal globally accessible (optional, but convenient for quick use in views)
window.Swal = Swal;

// Confirmation message in the browser console
console.log('phpLiteCore assets loaded successfully!');