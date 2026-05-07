function loadBookings() {
    fetch("php/user.php?action=bookings")
        .then(r => r.json())
        .then(data => {
            if (!data.logged_in) {
                window.location.href = "dashboard.php";
                return;
            }

            const list = document.getElementById("bookingsList");
            const bookings = data.bookings || [];

            document.getElementById("statTotal").textContent    = bookings.length;
            document.getElementById("statUpcoming").textContent = bookings.filter(b => b.display_status === 'upcoming').length;
            document.getElementById("statPast").textContent     = bookings.filter(b => b.display_status === 'past').length;

            if (!data.bookings || data.bookings.length === 0) {
                list.innerHTML = `
                    <div class="bookings-empty">
                        <i class="fa-regular fa-calendar-xmark"></i>
                        <p>You have no bookings yet.</p>
                        <a href="search-results.php" class="btn btn-primary">Find a Hotel</a>
                    </div>`;
                return;
            }

            list.innerHTML = bookings.map(b => `
                <div class="booking-card">
                    <img class="booking-hotel-img" src="${b.hotel_image || 'assets/images/hotel1.jpg'}" alt="${b.hotel_name}">
                    <div class="booking-card-left">
                        <div class="booking-meta">
                            <span class="status-badge status-${b.display_status}">${b.display_status}</span>
                            <span class="booking-type">${b.room_type}</span>
                        </div>
                        <h3>${b.hotel_name}</h3>
                        <p class="booking-room"><i class="fa-solid fa-bed"></i> ${b.room_name}</p>
                        <p class="booking-location"><i class="fa-solid fa-location-dot"></i> ${b.hotel_city}</p>
                    </div>
                    <div class="booking-card-mid">
                        <div class="booking-date">
                            <span>Check-in</span>
                            <strong>${b.check_in}</strong>
                        </div>
                        <div class="booking-arrow"><i class="fa-solid fa-arrow-right"></i></div>
                        <div class="booking-date">
                            <span>Check-out</span>
                            <strong>${b.check_out}</strong>
                        </div>
                    </div>
                    <div class="booking-card-right">
                        <div class="booking-price">
                            <span>${b.nights} night${b.nights > 1 ? 's' : ''}</span>
                            <strong>$${b.total_price}</strong>
                        </div>
                        ${b.can_cancel
                            ? `<button class="cancel-btn" onclick="cancelBooking(${b.id})">Cancel</button>`
                            : ``
                        }
                        ${b.display_status === 'past'
                            ? `<a class="review-btn" href="reviews_page.php?hotel_id=${b.hotel_id}">Write a Review</a>`
                            : ``
                        }
                    </div>
                </div>
            `).join('');
        })
        .catch(() => {
            document.getElementById("bookingsList").innerHTML =
                '<p class="error-message">Failed to load bookings.</p>';
        });
}

function cancelBooking(id) {
    if (!confirm("Cancel this booking?")) return;

    const form = new FormData();
    form.append("booking_id", id);

    fetch("php/user.php?action=cancel", { method: "POST", body: form })
        .then(r => r.json())
        .then(data => {
            const box = document.getElementById("messageBox");
            if (data.success) {
                box.innerHTML = '<div class="alert alert-success">Booking cancelled.</div>';
                loadBookings();
            } else {
                box.innerHTML = `<div class="alert alert-error">${data.message}</div>`;
            }
            setTimeout(() => box.innerHTML = '', 4000);
        });
}

loadBookings();

fetch("php/auth.php?action=session").then(r => r.json()).then(d => {
  if (d.user_name) document.getElementById("navUser").textContent = d.user_name;
});