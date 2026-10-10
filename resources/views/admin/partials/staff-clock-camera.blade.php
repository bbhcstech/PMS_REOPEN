            <div class="clock-camera-modal" id="clockCameraModal" aria-hidden="true" data-live-preserve>
                <div class="clock-camera-panel">
                    <div class="clock-camera-header">
                        <h5 class="mb-0 fw-bold">Capture Clock In Photo</h5>
                        <button type="button" class="clock-modal-btn danger" id="clockCameraClose"><i class="bx bx-x"></i> Close</button>
                    </div>
                    <div class="clock-camera-body">
                        <div class="clock-camera-preview" id="clockCameraPreview">
                            <video id="clockCameraVideo" autoplay playsinline muted></video>
                            <canvas id="clockCameraCanvas" style="display: none !important; position: absolute; width: 0; height: 0; pointer-events: none;"></canvas>
                            <img id="clockCameraPhoto" alt="Captured clock in photo">
                        </div>
                        <p class="employee-muted mt-3 mb-0">Take a clear face photo. You can flip camera on mobile, retake, then use photo for clock in.</p>
                    </div>
                    <div class="clock-camera-footer">
                        <button type="button" class="clock-modal-btn secondary" id="clockCameraFlip"><i class="bx bx-refresh"></i> Flip</button>
                        <button type="button" class="clock-modal-btn" id="clockCameraCapture"><i class="bx bx-camera"></i> Capture</button>
                        <button type="button" class="clock-modal-btn secondary" id="clockCameraRetake"><i class="bx bx-undo"></i> Retake</button>
                        <button type="button" class="clock-modal-btn success" id="clockCameraUse"><i class="bx bx-check"></i> Use Photo & Clock In</button>
                    </div>
                </div>
            </div>

