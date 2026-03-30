const mongoose = require('mongoose');

const clickLogSchema = new mongoose.Schema({
  url_id: { type: mongoose.Schema.Types.ObjectId, ref: 'Url', required: true, index: true },
  clicked_at: { type: Date, default: Date.now, index: true },
  referer: { type: String },
  user_agent: { type: String },
  ip_address: { type: String }
});

module.exports = mongoose.model('ClickLog', clickLogSchema);
