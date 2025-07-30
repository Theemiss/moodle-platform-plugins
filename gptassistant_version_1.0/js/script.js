const chatbox = document.getElementById('chatbox');
const toggleBtn = document.getElementById('toggleChatbox');
const chatboxBody = chatbox.querySelector('.chatbox-body');
const chatboxFooter = chatbox.querySelector('.chatbox-footer');
const chatContent = chatboxBody.querySelector('.hint-bubble'); 

function toggleChatbox() {
  const isClosed = chatboxBody.style.display === 'none' || chatboxBody.style.display === '';
  
  if (isClosed) {
    chatbox.style.height = '400px';
    chatboxBody.style.display = 'block';
    chatboxFooter.style.display = 'flex';
  } else {
    chatbox.style.height = '50px';
    chatboxBody.style.display = 'none';
    chatboxFooter.style.display = 'none';
  }
  
  const icon = toggleBtn.querySelector('.toggle-icon');
  icon.classList.toggle('rotated', isClosed);
}

document.getElementById('getHintNow').addEventListener('click', function() {
    document.getElementById('loadingIndicator').style.display = 'block';
});


setTimeout(function() {
    var loadingElement = document.getElementById('loading');
    var messageContentElement = document.getElementById('message-content');
    var hint = " "; 
    
    loadingElement.style.display = 'none';

    messageContentElement.innerHTML = '<div class="message bot-message"><span>' + hint + '</span></div>';
}, 3000); 
