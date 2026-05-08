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

let pricePerNight = 0;
const cleaning = 45;

function showError(message) {
  bookingError.textContent = message;
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
    return;
  }

  const base = nights * pricePerNight;
  const service = base * 0.12;
  const total = base + cleaning + service;

  nightsText.textContent = nights;
  basePrice.textContent = base.toFixed(2);
  serviceFee.textContent = service.toFixed(2);
  totalPrice.textContent = total.toFixed(2);
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
  }
});

loadRoom();

flatpickr("#checkIn",  { minDate: "today", onChange: calculatePrice });
flatpickr("#checkOut", { minDate: "today", onChange: calculatePrice });
