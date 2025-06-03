<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>MMU PARK - Admin Slot Management</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f3f4f6;
            padding: 20px;
            min-height: 100vh;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
        }

        .header {
            margin-bottom: 20px;
            text-align: center;
        }

        .header h1 {
            color: #1f2937;
            font-size: 2rem;
            font-weight: bold;
            margin-bottom: 10px;
        }

        .header p {
            color: #6b7280;
            margin-bottom: 5px;
        }

        .header .info {
            color: #9ca3af;
            font-size: 0.875rem;
        }

        .map-container {
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            padding: 20px;
            position: relative;
            overflow: hidden;
            min-height: 600px;
        }

        .background-image {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-image: url('MMUPARK.png');
            background-size: contain;
            background-position: center;
            background-repeat: no-repeat;
            opacity: 0.3;
            z-index: 1;
        }

        .overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(255, 255, 255, 0.2);
            z-index: 2;
        }

        .interactive-layer {
            position: relative;
            z-index: 10;
            min-height: 600px;
            width: 100%;
        }

        .slot-button {
            position: absolute;
            border: 2px solid;
            border-radius: 6px;
            color: white;
            font-size: 12px;
            font-weight: bold;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s ease;
            z-index: 20;
        }

        .slot-button:hover {
            transform: scale(1.05);
        }

        .slot-button.available {
            background-color: #10b981;
            border-color: #059669;
        }

        .slot-button.available:hover {
            background-color: #059669;
        }

        .slot-button.blocked {
            background-color: #ef4444;
            border-color: #dc2626;
        }

        .slot-button.blocked:hover {
            background-color: #dc2626;
        }

        /* Specific clip-path for FOM button - creates an angled/diamond-like shape */
        #topLeft1 {
            clip-path: polygon(61% 0, 86% 0, 86% 92%, 7% 91%, 17% 32%);
        }
		
		 #centralLeft1 {
		    clip-path: polygon(42% 0, 86% 32%, 92% 53%, 51% 95%, 27% 94%, 26% 49%, 6% 27%, 6% 27%);
		 }
		 #centralLeft2 {
		    clip-path: polygon(8% 2%, 78% 71%, 84% 67%, 94% 77%, 91% 97%, 16% 96%, 6% 41%);
		 }
		 #centralLeft3 {
		    clip-path: polygon(0 29%, 26% 0, 99% 79%, 61% 100%);
		 }
		 #centralRight1 {
		    clip-path: polygon(100% 10%, 100% 36%, 59% 36%, 38% 88%, 0 89%, 2% 11%);
		 }
		 #centralRight2 {
		    clip-path: polygon(27% 0, 46% 0, 47% 100%, 29% 100%);
		 }
		 #centralRight3 {
		    clip-path: polygon(27% 0, 46% 0, 47% 100%, 29% 100%);
		 }
		 #centralTop {
		    clip-path: polygon(27% 0, 46% 0, 47% 100%, 29% 100%);
		 }
		 #centralBottom1 {
		    clip-path: polygon(67% 0, 92% 6%, 17% 100%, 0 87%);
		 }
		 #centralBottom2 {
		    clip-path: polygon(12% 4%, 85% 4%, 89% 91%, 49% 91%);
		 }
		 #centralBottom3 {
		    clip-path: polygon(10% 21%, 90% 20%, 89% 67%, 9% 67%);
		 }
		 #centralBottom4 {
		   clip-path: polygon(18% 38%, 41% 20%, 100% 73%, 78% 95%);
		 }
		 #bottomLeft2 {
		   clip-path: polygon(0 24%, 100% 24%, 100% 44%, 0 43%);
		 }
		 #bottomRight1 {
		   clip-path: polygon(0 24%, 100% 24%, 100% 44%, 0 43%);
		 }
		 #bottomRight2 {
		   clip-path: polygon(18% 4%, 65% 4%, 65% 84%, 17% 84%);
		 }
		 #bottomRight3 {
		   clip-path: polygon(18% 4%, 65% 4%, 65% 84%, 17% 84%);
		 }
		 #bottomRight4 {
		   clip-path: polygon(24% 0, 48% 0, 51% 100%, 26% 100%);
		 }
		 #bottomRight5 {
		   clip-path: polygon(24% 0, 58% 0, 58% 81%, 26% 81%);
		 }
		 

        .status-panel {
            margin-top: 20px;
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            padding: 20px;
        }

        .status-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .status-item {
            font-size: 14px;
            font-weight: 600;
        }

        .available-count {
            color: #059669;
        }

        .blocked-count {
            color: #dc2626;
        }

        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 1000;
            align-items: center;
            justify-content: center;
        }

        .modal.show {
            display: flex;
        }

        .modal-content {
            background: white;
            border-radius: 12px;
            padding: 24px;
            width: 400px;
            max-width: 90vw;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
        }

        .modal-header {
            font-size: 1.25rem;
            font-weight: bold;
            color: #1f2937;
            margin-bottom: 20px;
        }

        .form-group {
            margin-bottom: 16px;
        }

        .form-label {
            display: block;
            font-size: 14px;
            font-weight: 500;
            color: #374151;
            margin-bottom: 8px;
        }

        .form-input {
            width: 100%;
            padding: 12px;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            font-size: 14px;
        }

        .form-input:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }

        .button-group {
            display: flex;
            gap: 12px;
            margin-top: 24px;
        }

        .btn {
            flex: 1;
            padding: 12px 16px;
            border: none;
            border-radius: 8px;
            font-weight: 500;
            cursor: pointer;
            transition: background-color 0.2s ease;
        }

        .btn-primary {
            background-color: #ef4444;
            color: white;
        }

        .btn-primary:hover:not(:disabled) {
            background-color: #dc2626;
        }

        .btn-primary:disabled {
            background-color: #d1d5db;
            cursor: not-allowed;
        }

        .btn-secondary {
            background-color: #6b7280;
            color: white;
        }

        .btn-secondary:hover {
            background-color: #4b5563;
        }

        /* Tooltip */
        .slot-button[title]:hover::after {
            content: attr(title);
            position: absolute;
            bottom: 100%;
            left: 50%;
            transform: translateX(-50%);
            background: #1f2937;
            color: white;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 11px;
            white-space: nowrap;
            margin-bottom: 4px;
            z-index: 1000;
        }

        /* Responsive design */
        @media (max-width: 768px) {
            .container {
                padding: 10px;
            }
            
            .header h1 {
                font-size: 1.5rem;
            }
            
            .slot-button {
                font-size: 10px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <h1>MMU PARK - Admin Slot Management</h1>
            <p>Click GREEN areas on the map to block them | Click RED areas to unblock them</p>
            <p class="info">Background: MMUPARK.png with interactive overlay buttons</p>
        </div>

        <!-- Map Container -->
        <div class="map-container">
            <!-- Background Image -->
            <div class="background-image"></div>
            
            <!-- Overlay -->
            <div class="overlay"></div>
            
            <!-- Interactive Layer -->
            <div class="interactive-layer">
                <!-- Green Slot Buttons positioned over the map -->
                
                <!-- Top left area slots -->
                <div class="slot-button available" id="topLeft1" style="top: 30%; left: 37.5%; width: 5%; height: 6.7%;" title="Click to block this area">FOM</div>
                
                
                <!-- Main central oval area - left side -->
                <div class="slot-button available" id="centralLeft1" style="top: 23%; left: 50%; width: 5.8%; height: 8.3%;" title="Click to block this area">CL1</div>
                <div class="slot-button available" id="centralLeft2" style="top: 43%; left: 39%; width: 5.8%; height: 8.3%;" title="Click to block this area">CL2</div>
                <div class="slot-button available" id="centralLeft3" style="top: 68%; left: 40%; width: 5.8%; height: 8.3%;" title="Click to block this area">CL3</div>
                
                <!-- Main central oval area - right side -->
                <div class="slot-button available" id="centralRight1" style="top: 63%; right: 38%; width: 5.8%; height: 8.3%;" title="Click to block this area">CR1</div>
                <div class="slot-button available" id="centralRight2" style="top: 65%; right: 58%; width: 5.8%; height: 8.3%;" title="Click to block this area">CR2</div>
                <div class="slot-button available" id="centralRight3" style="top: 53%; right: 53%; width: 5.8%; height: 8.3%;" title="Click to block this area">CR3</div>
                
                <!-- Central top area -->
                <div class="slot-button available" id="centralTop" style="top: 50%; left: 52%; transform: translateX(-50%); width: 6.7%; height: 8.3%;" title="Click to block this area">CT</div>
                
                <!-- Central bottom areas -->
                <div class="slot-button available" id="centralBottom1" style="top: 37%; left: 50%; width: 5%; height: 7.5%;" title="Click to block this area">CB1</div>
                <div class="slot-button available" id="centralBottom2" style="top: 48%; left: 54%; width: 3%; height: 7.5%;" title="Click to block this area">CB2</div>
                <div class="slot-button available" id="centralBottom3" style="top: 57%; right: 37%; width: 5%; height: 7.5%;" title="Click to block this area">CB3</div>
                <div class="slot-button available" id="centralBottom4" style="top: 78%; right: 44%; width: 5%; height: 7.5%;" title="Click to block this area">CB4</div>
                
                <!-- Bottom left areas -->
                <div class="slot-button available" id="bottomLeft1" style="top: 50%; left: 36%; width: 3%; height: 6.7%;" title="Click to block this area">BL1</div>
                <div class="slot-button available" id="bottomLeft2" style="top: 68%; left: 63%; width: 5%; height: 6.7%;" title="Click to block this area">BL2</div>
                
                <!-- Bottom right extension areas -->
                <div class="slot-button available" id="bottomRight1" style="top: 72%; right: 33%; width: 4.6%; height: 5.8%;" title="Click to block this area">BR1</div>
                <div class="slot-button available" id="bottomRight2" style="top: 75%; right: 31%; width: 4.6%; height: 5.8%;" title="Click to block this area">BR2</div>
                <div class="slot-button available" id="bottomRight3" style="top: 82%; right: 31%; width: 4.6%; height: 5.8%;" title="Click to block this area">BR3</div>
                <div class="slot-button available" id="bottomRight4" style="top: 52%; right: 44%; width: 3%; height: 5.8%;" title="Click to block this area">BR4</div>
                <div class="slot-button available" id="bottomRight5" style="top: 74%; right: 42%; width: 4%; height: 5.8%;" title="Click to block this area">BR5</div>
            </div>
        </div>

        <!-- Status Panel -->
        <div class="status-panel">
            <div class="status-row">
                <div class="status-item">
                    Available: <span class="available-count" id="availableCount">17</span>
                </div>
                <div class="status-item">
                    Blocked: <span class="blocked-count" id="blockedCount">0</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Block Slot Modal -->
    <div class="modal" id="blockModal">
        <div class="modal-content">
            <h3 class="modal-header" id="modalTitle">Block Slot</h3>
            
            <div class="form-group">
                <label class="form-label" for="blockDate">📅 Block Date</label>
                <input type="date" class="form-input" id="blockDate">
            </div>
            
            <div class="form-group">
                <label class="form-label" for="blockTime">🕐 Block Time</label>
                <input type="time" class="form-input" id="blockTime">
            </div>
            
            <div class="button-group">
                <button class="btn btn-primary" id="confirmBlock">Block Slot</button>
                <button class="btn btn-secondary" id="cancelBlock">Cancel</button>
            </div>
        </div>
    </div>

    <script>
        // Slot management system
        const slots = {};
        let selectedSlot = null;

        // Initialize all slots
        function initializeSlots() {
            const slotElements = document.querySelectorAll('.slot-button');
            slotElements.forEach(element => {
                slots[element.id] = {
                    id: element.id,
                    name: element.textContent,
                    blocked: false,
                    blockDate: '',
                    blockTime: '',
                    element: element
                };
                
                // Add click event listener
                element.addEventListener('click', handleSlotClick);
            });
            updateStatusCount();
        }

        // Handle slot button clicks
        function handleSlotClick(event) {
            const slotId = event.target.id;
            const slot = slots[slotId];
            
            if (slot.blocked) {
                // Unblock the slot
                unblockSlot(slotId);
            } else {
                // Show modal to block the slot
                showBlockModal(slotId);
            }
        }

        // Show block modal
        function showBlockModal(slotId) {
            selectedSlot = slotId;
            const slot = slots[slotId];
            
            document.getElementById('modalTitle').textContent = `Block Slot: ${slot.name}`;
            document.getElementById('blockDate').value = '';
            document.getElementById('blockTime').value = '';
            document.getElementById('blockModal').classList.add('show');
            
            // Set minimum date to today
            const today = new Date().toISOString().split('T')[0];
            document.getElementById('blockDate').min = today;
        }

        // Hide block modal
        function hideBlockModal() {
            document.getElementById('blockModal').classList.remove('show');
            selectedSlot = null;
        }
        // Block a slot
function blockSlot(slotId, date, time) {
    fetch(route('slots.update', { slot: slotId }), {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({
            status: 'blocked',
            block_date: date,
            block_time: time
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const slot = slots[slotId];
            slot.blocked = true;
            slot.blockDate = date;
            slot.blockTime = time;
            
            // Update visual appearance
            slot.element.classList.remove('available');
            slot.element.classList.add('blocked');
            slot.element.innerHTML = '🔒';
            slot.element.title = `Blocked until ${date} ${time} - Click to unblock`;
            
            updateStatusCount();
        }
    })
    .catch(error => console.error('Error:', error));
}

// Unblock a slot
function unblockSlot(slotId) {
    fetch(route('slots.update', { slot: slotId }), {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({
            status: 'available',
            block_date: null,
            block_time: null
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const slot = slots[slotId];
            slot.blocked = false;
            slot.blockDate = '';
            slot.blockTime = '';
            
            // Update visual appearance
            slot.element.classList.remove('blocked');
            slot.element.classList.add('available');
            slot.element.innerHTML = slot.name;
            slot.element.title = 'Click to block this area';
            
            updateStatusCount();
        }
    })
    .catch(error => console.error('Error:', error));
}
        // Block a slot
        function blockSlot(slotId, date, time) {
            const slot = slots[slotId];
            slot.blocked = true;
            slot.blockDate = date;
            slot.blockTime = time;
            
            // Update visual appearance
            slot.element.classList.remove('available');
            slot.element.classList.add('blocked');
            slot.element.innerHTML = '🔒';
            slot.element.title = `Blocked until ${date} ${time} - Click to unblock`;
            
            updateStatusCount();
        }

        // Unblock a slot
        function unblockSlot(slotId) {
            const slot = slots[slotId];
            slot.blocked = false;
            slot.blockDate = '';
            slot.blockTime = '';
            
            // Update visual appearance
            slot.element.classList.remove('blocked');
            slot.element.classList.add('available');
            slot.element.innerHTML = slot.name;
            slot.element.title = 'Click to block this area';
            
            updateStatusCount();
        }

        // Update status count
        function updateStatusCount() {
            const availableCount = Object.values(slots).filter(slot => !slot.blocked).length;
            const blockedCount = Object.values(slots).filter(slot => slot.blocked).length;
            
            document.getElementById('availableCount').textContent = availableCount;
            document.getElementById('blockedCount').textContent = blockedCount;
        }

        // Validate block form
        function validateBlockForm() {
            const date = document.getElementById('blockDate').value;
            const time = document.getElementById('blockTime').value;
            const confirmButton = document.getElementById('confirmBlock');
            
            if (date && time) {
                confirmButton.disabled = false;
            } else {
                confirmButton.disabled = true;
            }
        }

        // Event listeners
        document.addEventListener('DOMContentLoaded', function() {
            initializeSlots();
            
            // Modal event listeners
            document.getElementById('confirmBlock').addEventListener('click', function() {
                const date = document.getElementById('blockDate').value;
                const time = document.getElementById('blockTime').value;
                
                if (selectedSlot && date && time) {
                    blockSlot(selectedSlot, date, time);
                    hideBlockModal();
                }
            });
            
            document.getElementById('cancelBlock').addEventListener('click', hideBlockModal);
            
            // Form validation
            document.getElementById('blockDate').addEventListener('input', validateBlockForm);
            document.getElementById('blockTime').addEventListener('input', validateBlockForm);
            
            // Close modal when clicking outside
            document.getElementById('blockModal').addEventListener('click', function(event) {
                if (event.target === this) {
                    hideBlockModal();
                }
            });
            
            // Initial form validation
            validateBlockForm();
        });
    </script>
</body>
</html>