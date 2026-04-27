
<script setup>
import { ref, computed } from "vue";

const search = ref("");

const newContact = ref({
  name: "",
  email: "",
  phone: "",
});

const contacts = ref([
  { id: 1, name: "John Doe", email: "john@example.com", phone: "123-456-7890" },
  { id: 2, name: "Jane Smith", email: "jane@example.com", phone: "987-654-3210" },
]);

const filteredContacts = computed(() => {
  return contacts.value.filter((contact) =>
    contact.name.toLowerCase().includes(search.value.toLowerCase())
  );
});

function addContact() {
  contacts.value.push({
    id: Date.now(),
    ...newContact.value,
  });

  newContact.value = { name: "", email: "", phone: "" };
}

function removeContact(id) {
  contacts.value = contacts.value.filter((contact) => contact.id !== id);
}
</script>

<style scoped>
.layout {
  display: flex;
  height: 100vh;
  font-family: Arial, sans-serif;
}

/* LEFT SIDE */
.info-section {
  width: 35%;
  padding: 40px;
  background: #f5f5f5;
  display: flex;
  flex-direction: column;
  justify-content: flex-start;
  
}

.info-section h1 {
  font-size: 32px;
  margin-bottom: 20px;
}

.info-section p {
  font-size: 18px;
  line-height: 1.6;
  margin-bottom: 500px;
}

/* RIGHT SIDE */
.map-section {
  width: 65%;
}

.map-section iframe {
  width: 100%;
  height: 100%;
  border: none;
}

.location-grid {
  display: grid;
  grid-template-columns: 120px 1fr;
  gap: 10px 15px;
  margin-top: 20px;
}

.label {
  font-weight: bold;
}

.value {
  line-height: 1.6;
}
</style>


<template>
  <div class="layout">
    <!-- LEFT SIDE -->
   <div class="info-section">
  <h1>Our Location</h1>

  <div class="location-grid">
    <div class="label">Address:</div>
    <div class="value">
      South Harbor Pier, 15th St<br />
      Port Area, Manila<br />
      Metro Manila, Philippines
    </div>

    <div class="label">Email:</div>
    <div class="value">cgfleet.crs@gmail.com</div>

    <div class="label">Phone:</div>
    <div class="value">+63 9273881123</div>

    <div class="label">Facebook:</div>
    <div class="value">Coast Guard Fleet</div>
  </div>
</div>


    <!-- RIGHT SIDE (Large Map) -->
    <div class="map-section">
      <iframe
        src="https://www.google.com/maps?q=South+Harbor+Pier,+15th+St,+Port+Area,+Manila,+Metro+Manila&output=embed"
        allowfullscreen
        loading="lazy">
      </iframe>
    </div>

  </div>
</template>