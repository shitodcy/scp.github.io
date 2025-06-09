// routes/api.js
const express = require('express');
const router = express.Router();

router.get('/dashboard', (req, res) => {
  res.json({
    totalUsers: 123,
    totalPosts: 45
  });
});

module.exports = router;
