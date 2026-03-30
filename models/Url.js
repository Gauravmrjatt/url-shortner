const mongoose = require('mongoose');

const urlSchema = new mongoose.Schema({
  original_url: { type: String, required: true },
  short_code: { type: String, required: true, unique: true, index: true },
  custom_alias: { type: String, unique: true, sparse: true, index: true },
  clicks: { type: Number, default: 0 },
  created_at: { type: Date, default: Date.now, index: true },
  expires_at: { type: Date },
  is_active: { type: Boolean, default: true },
  user_id: { type: String }
});

module.exports = mongoose.model('Url', urlSchema);
