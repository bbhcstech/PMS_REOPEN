<!-- //layout//footer.blade.php -->

@php
  $adminRefreshVersion = file_exists(public_path('admin/assets/css/pms-refresh.css')) ? filemtime(public_path('admin/assets/css/pms-refresh.css')) : time();
@endphp
<link rel="stylesheet" href="{{ asset('admin/assets/css/pms-refresh.css') }}?v={{ $adminRefreshVersion }}">

<!-- Footer -->
            <footer class="content-footer footer bg-footer-theme">
              <div class="container-xxl">
                <div
                  class="footer-container d-flex align-items-center justify-content-between py-4 flex-md-row flex-column">
                </div>
              </div>
            </footer>
            <!-- / Footer -->

            <div class="content-backdrop fade"></div>
            </div>
            <!-- / container-xxl flex-grow-1 container-p-y -->
          </div>
          <!-- / Content wrapper -->
        </div>
        <!-- / Layout page -->
      </div>
      <!-- / Layout container -->

      <!-- Overlay -->
      <div class="layout-overlay"></div>
    </div>
    <!-- / Layout wrapper -->

    {{-- <div class="buy-now">
      <a
        href="https://themeselection.com/item/sneat-dashboard-pro-bootstrap/"
        target="_blank"
        class="btn btn-danger btn-buy-now"
        >Upgrade to Pro</a
      >
    </div> --}}

    <!-- Core JS -->

    <script src="{{ asset('admin/assets/vendor/libs/jquery/jquery.js')}}"></script>

    <script src="{{ asset('admin/assets/vendor/libs/popper/popper.js')}}"></script>
    <script src="{{ asset('admin/assets/vendor/js/bootstrap.js')}}"></script>

    <script src="{{ asset('admin/assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.js')}}"></script>

    <script src="{{ asset('admin/assets/vendor/js/menu.js')}}"></script>

    <!-- endbuild -->

    <!-- Vendors JS -->
    <script src="{{ asset('admin/assets/vendor/libs/apex-charts/apexcharts.js')}}"></script>

    <!-- Main JS -->

    <script src="{{ asset('admin/assets/js/main.js')}}"></script>

    <!-- Page JS -->
    <script src="{{ asset('admin/assets/js/dashboards-analytics.js')}}"></script>

     <!-- ✅ Add Select2 CDN JS here -->
    <!-- jQuery (required for Select2) -->
    <!--<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>-->
    <!-- Select2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <!-- Place this tag before closing body tag for github widget button. -->
    <script async defer src="https://buttons.github.io/buttons.js"></script>

    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>



        <script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
        <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>
        <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.print.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.3.0-beta.1/pdfmake.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.3.0-beta.1/vfs_fonts.js"></script>
        <!-- Add before </body> -->
        <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.14.0-beta3/js/bootstrap-select.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
        <script>
          (function () {
            var root = document.documentElement;
            var toggles = document.querySelectorAll('.pms-theme-toggle');

            function applyTheme(theme) {
              root.setAttribute('data-pms-theme', theme);
              localStorage.setItem('pms-theme', theme);
              toggles.forEach(function (button) {
                var icon = button.querySelector('.theme-toggle-icon');
                button.setAttribute('aria-label', theme === 'dark' ? 'Switch to light mode' : 'Switch to dark mode');
                button.setAttribute('title', theme === 'dark' ? 'Light mode' : 'Dark mode');
                if (icon) {
                  icon.classList.toggle('bx-sun', theme === 'dark');
                  icon.classList.toggle('bx-moon', theme !== 'dark');
                }
              });
            }

            applyTheme(localStorage.getItem('pms-theme') || root.getAttribute('data-pms-theme') || 'light');
            toggles.forEach(function (button) {
              button.addEventListener('click', function () {
                applyTheme(root.getAttribute('data-pms-theme') === 'dark' ? 'light' : 'dark');
              });
            });
          })();
        </script>
        @yield('scripts')
        @yield('js')
        @stack('js')
        @stack('scripts')  <!-- ✅ Correct way to render pushed scripts -->
          </body>
        </html>
