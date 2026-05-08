const params = new URLSearchParams(location.search);
const hotelId = params.get('hotel_id') || 1;
let allRooms = [];

function esc(text) {
  return String(text ?? '').replace(/[&<>"]/g, s => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;'}[s]));
}

function stars(n) {
  return '★'.repeat(Number(n || 0)) + '☆'.repeat(5 - Number(n || 0));
}

function showRooms(type = 'all') {
  const grid = document.getElementById('roomsGrid');
  const rooms = type === 'all' ? allRooms : allRooms.filter(r => String(r.type).toLowerCase() === type.toLowerCase());

  if (rooms.length === 0) {
    grid.innerHTML = '<div class="empty">No rooms found for this filter.</div>';
    return;
  }

  grid.innerHTML = rooms.map(room => `
    <article class="room-card">
      <div class="room-image">
        <img src="${esc(room.image || 'assets/images/room1.jpg')}" alt="${esc(room.name)}">
        <span class="tag ${room.is_available == 1 ? '' : 'danger'}">${room.is_available == 1 ? esc(room.type) : 'Unavailable'}</span>
      </div>
      <div class="room-body">
        <div class="room-title-row">
          <h3>${esc(room.name)}</h3>
          <strong>$${Number(room.price_per_night).toFixed(0)}</strong>
        </div>
        <p><i class="fa-solid fa-location-dot"></i> ${esc(document.getElementById('hotelCity').textContent)}</p>
        <div class="room-icons">
          <span><i class="fa-solid fa-bed"></i> ${esc(room.type)}</span>
          <span><i class="fa-solid fa-ruler-combined"></i> 45m²</span>
          <span><i class="fa-solid fa-wifi"></i> Free Wi-Fi</span>
        </div>
        <a class="primary-btn" href="room-detail.html?room_id=${room.id}">View Details</a>
      </div>
    </article>
  `).join('');
}

fetch(`php/hotels.php?action=hotel&hotel_id=${hotelId}`)
  .then(res => res.json())
  .then(data => {
    if (!data.success) throw new Error(data.message);

    const h = data.hotel;
    allRooms = data.rooms;

    document.getElementById('hotelName').textContent = h.name;
    document.getElementById('hotelNameSmall').textContent = h.name;
    document.getElementById('hotelInfo').textContent = `${h.location} • ${data.average_rating}/5 (${data.review_count} Reviews)`;
    document.getElementById('hotelCity').textContent = h.city;
    document.getElementById('hotelDesc').textContent = h.description;
    document.getElementById('hotelStars').textContent = stars(h.stars);
    document.getElementById('reviewsLink').href = `reviews_page.php?hotel_id=${hotelId}`;
    document.getElementById('hotelCover').style.backgroundImage = `linear-gradient(90deg, rgba(15,23,42,.75), rgba(15,23,42,.15)), url('${h.image || 'assets/images/hotel1.jpg'}')`;

    showRooms();
  })
  .catch(error => {
    document.getElementById('roomsGrid').innerHTML = `<div class="empty">${esc(error.message)}</div>`;
  });

document.querySelectorAll('.filter').forEach(button => {
  button.onclick = () => {
    document.querySelectorAll('.filter').forEach(b => b.classList.remove('active'));
    button.classList.add('active');
    showRooms(button.dataset.type);
  };
});

fetch("php/auth.php?action=session").then(r => r.json()).then(d => {
  if (d.user_name) document.getElementById("navUser").textContent = d.user_name;
});