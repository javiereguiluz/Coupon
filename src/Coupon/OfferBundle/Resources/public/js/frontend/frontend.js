/**
 * Creates a countdown to the given expiration date and displays it as a timer
 * that updates every second.
 */
function showCountdown(id, date) {
    var hours, minutes, seconds;
    
    var now = new Date();
    var expiresAt = new Date(date.year, date.month, date.day, date.hour, date.minute, date.second);
    
    var left = Math.floor( (expiresAt.getTime() - now.getTime()) / 1000 );
    
    if (left < 0) {
        countdown = '-';
    }
    else {
        hours = Math.floor(left/3600);
        left = left % 3600;
        
        minutes = Math.floor(left/60);
        left = left % 60;
        
        seconds = Math.floor(left);
        
        countdown = (hours < 10    ? '0' + hours    : hours)    + 'h '
                  + (minutes < 10  ? '0' + minutes  : minutes)  + 'm '
                  + (seconds < 10 ? '0' + seconds : seconds) + 's ';
        
        setTimeout(function() {
            showCountdown(id, date);
        }, 1000);
    }
    
    document.getElementById(id).innerHTML = countdown;
}