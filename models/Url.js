const mongoose = require('mongoose');

const urlSchema = new mongoose.Schema({
  original_url: { type: String, required: true },
  short_code: { type: String, required: true, unique: true },
  custom_alias: { type: String, unique: true, sparse: true },
  clicks: { type: Number, default: 0 },
  created_at: { type: Date, default: Date.now },
  expires_at: { type: Date },
  is_active: { type: Boolean, default: true },
  user_id: { type: String }
});

urlSchema.index({ short_code: 1 });
urlSchema.index({ custom_alias: 1 });
urlSchema.index({ created_at: -1 });

module.exports = mongoose.model('Url', urlSchema);
