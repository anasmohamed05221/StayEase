const params = new URLSearchParams(window.location.search);
const roomId = params.get("room_id");
const urlError = params.get("error");
if (urlError) document.addEventListener("DOMContentLoaded", () => showError(urlError));

const roomCard = document.getElementById("roomCard");
const roomIdInput = document.getElementById("roomIdInput");
const checkIn = document.getElementById("checkIn");
const checkOut = document.getElementById("checkOut");
const checkInHidden = document.getElementById("checkInHidden");
const checkOutHidden = document.getElementById("checkOutHidden");

const nightsText = document.getElementById("nightsText");
const basePrice = document.getElementById("basePrice");
const serviceFee = document.getElementById("serviceFee");
const totalPrice = document.getElementById("totalPrice");
const bookingError = document.getElementById("bookingError");
const payBtn = document.querySelector(".pay-btn");

let pricePerNight = 0;
const cleaning = 45;
let roomIsAvailable = false;

function showError(message) {
  bookingError.textContent = message;
}

function setAvailabilityBadge(state) {
  let badge = document.getElementById("availabilityBadge");
  if (!badge) {
    badge = document.createElement("p");
    badge.id = "availabilityBadge";
    badge.style.cssText = "margin-top:10px;font-weight:600;font-size:0.9rem;";
    checkOut.closest(".date-grid").after(badge);
  }
  if (state === "available") {
    badge.textContent = "✓ Room is available for these dates";
    badge.style.color = "#16a34a";
    payBtn.disabled = false;
    payBtn.style.opacity = "1";
  } else if (state === "unavailable") {
    badge.textContent = "✗ Room is already booked for these dates";
    badge.style.color = "#dc2626";
    payBtn.disabled = true;
    payBtn.style.opacity = "0.5";
  } else {
    badge.textContent = "";
    payBtn.disabled = false;
    payBtn.style.opacity = "1";
  }
}

async function checkAvailability() {
  if (!roomIdInput.value || !checkIn.value || !checkOut.value) return;
  if (new Date(checkOut.value) <= new Date(checkIn.value)) return;

  try {
    const response = await fetch(
      `php/booking.php?action=check&room_id=${encodeURIComponent(roomIdInput.value)}&check_in=${encodeURIComponent(checkIn.value)}&check_out=${encodeURIComponent(checkOut.value)}`
    );
    const data = await response.json();
    if (!data.success) return;

    roomIsAvailable = data.available;
    setAvailabilityBadge(data.available ? "available" : "unavailable");
  } catch {
    // silently ignore network errors — server-side overlap check still guards
  }
}

function calculatePrice() {
  bookingError.textContent = "";

  checkInHidden.value = checkIn.value;
  checkOutHidden.value = checkOut.value;

  if (!checkIn.value || !checkOut.value) {
    nightsText.textContent = "0";
    basePrice.textContent = "0.00";
    serviceFee.textContent = "0.00";
    totalPrice.textContent = "0.00";
    setAvailabilityBadge("reset");
    roomIsAvailable = false;
    return;
  }

  const start = new Date(checkIn.value);
  const end = new Date(checkOut.value);
  const nights = (end - start) / (1000 * 60 * 60 * 24);

  if (nights <= 0) {
    nightsText.textContent = "0";
    basePrice.textContent = "0.00";
    serviceFee.textContent = "0.00";
    totalPrice.textContent = "0.00";
    showError("Check-out date must be after check-in date.");
    setAvailabilityBadge("reset");
    roomIsAvailable = false;
    return;
  }

  const base = nights * pricePerNight;
  const service = base * 0.12;
  const total = base + cleaning + service;

  nightsText.textContent = nights;
  basePrice.textContent = base.toFixed(2);
  serviceFee.textContent = service.toFixed(2);
  totalPrice.textContent = total.toFixed(2);

  checkAvailability();
}

async function loadRoom() {
  if (!roomId) {
    roomCard.innerHTML = `<div class="error-box">No room selected. Please go back and choose a room first.</div>`;
    return;
  }
  try {
    const response = await fetch(`php/booking.php?action=room&room_id=${encodeURIComponent(roomId)}`);
    const data = await response.json();

    if (data.redirect) {
      window.location.href = data.redirect;
      return;
    }

    if (!data.success) {
      roomCard.innerHTML = `<div class="error-box">${data.message}</div>`;
      return;
    }

    const room = data.room;
    pricePerNight = parseFloat(room.price_per_night);
    roomIdInput.value = room.id;

    roomCard.innerHTML = `
      <img src="${room.image || 'assets/images/room-placeholder.jpg'}" alt="Room Image" class="room-photo">
      <div class="room-info">
        <div class="room-badges">
          <span class="badge">${room.type}</span>
          ${room.avg_rating ? `<span class="rating">★ ${room.avg_rating} Rating</span>` : ``}
        </div>
        <h1>${room.name}</h1>
        <p class="location"><i class="fa-solid fa-location-dot"></i> &nbsp ${room.hotel_name}</p>
      </div>
    `;

  } catch (error) {
    roomCard.innerHTML = `<div class="error-box">Could not load room details.</div>`;
  }
}

document.getElementById("bookingForm").addEventListener("submit", function(e) {
  calculatePrice();

  if (!roomIdInput.value) {
    e.preventDefault();
    showError("Room data is missing.");
    return;
  }

  if (!checkIn.value || !checkOut.value) {
    e.preventDefault();
    showError("Please select check-in and check-out dates.");
    return;
  }

  if (new Date(checkOut.value) <= new Date(checkIn.value)) {
    e.preventDefault();
    showError("Check-out date must be after check-in date.");
    return;
  }

  if (!roomIsAvailable) {
    e.preventDefault();
    showError("This room is not available for the selected dates.");
  }
});

loadRoom();

flatpickr("#checkIn",  { minDate: "today", onChange: calculatePrice });
flatpickr("#checkOut", { minDate: "today", onChange: calculatePrice });
