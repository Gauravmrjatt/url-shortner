const mongoose = require('mongoose');

const clickLogSchema = new mongoose.Schema({
  url_id: { type: mongoose.Schema.Types.ObjectId, ref: 'Url', required: true },
  clicked_at: { type: Date, default: Date.now },
  referer: { type: String },
  user_agent: { type: String },
  ip_address: { type: String }
});

clickLogSchema.index({ url_id: 1 });
clickLogSchema.index({ clicked_at: -1 });

module.exports = mongoose.model('ClickLog', clickLogSchema);
