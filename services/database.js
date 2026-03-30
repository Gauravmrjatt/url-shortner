const mongoose = require('mongoose');

async function connect() {
  const uri = process.env.MONGODB_URI || 'mongodb://localhost:27017/urlshortner';
  await mongoose.connect(uri);
  console.log('Connected to MongoDB');
}

module.exports = { connect };
