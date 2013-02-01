import java.util.List;

public class SystemCommandExecutorThread extends Thread {
    private SystemCommandExecutor commandExecutor;
 
    public SystemCommandExecutorThread(final List<String> commands){
        commandExecutor = new SystemCommandExecutor(commands);
        
    }
    
    public void run() {
        try{
            commandExecutor.executeCommand();
        } catch (Exception e){//Catch exception if any
            System.err.println("Error: " + e.getMessage());
        }
    }
 
}
